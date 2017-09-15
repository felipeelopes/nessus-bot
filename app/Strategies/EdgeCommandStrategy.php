<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\User;
use Application\Models\UserGamertag;
use Application\Services\CommandService;
use Application\Services\KeyboardService;
use Application\Services\MockupService;
use Application\Services\Telegram\BotService;
use Application\Strategies\Contracts\UserStrategyContract;
use Artisan;
use Carbon\Carbon;

class EdgeCommandStrategy implements UserStrategyContract
{
    /**
     * Returns the list of administrators.
     * @param BotService $botService BotService instance.
     * @return string[]
     */
    public static function getAdministratorsList($botService): array
    {
        $chatMembers = $botService->getChatAdministrators();
        $admins      = [];

        foreach ($chatMembers as $chatMember) {
            if ($chatMember->user->is_bot) {
                continue;
            }

            $admins[] = $chatMember->user->getMention();
        }

        sort($admins);

        return array_map(function ($userMention) {
            return trans('UserRules.adminItem', [
                'username' => $userMention,
            ]);
        }, $admins);
    }

    /**
     * Process the Gamertag command.
     * @param Update $update Update instance.
     * @throws \Exception
     */
    private static function commandGamertag(Update $update): void
    {
        $botService     = BotService::getInstance();
        $messageCommand = $update->message->getCommand();

        assert($messageCommand !== null);

        if ($messageCommand->entities->isEmpty()) {
            $commandText = $messageCommand->getTextArgument();

            if ($commandText !== null) {
                if (strlen($commandText) < 3) {
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('EdgeCommand.searchGtFewLetters'))
                        ->publish();

                    return;
                }

                $keyboardService = KeyboardService::getInstance();

                /** @var UserGamertag $gamertagSingle */
                $gamertagSingleQuery = UserGamertag::query();
                $gamertagSingleQuery->where('gamertag_value', 'REGEXP', $keyboardService->generateFuzzyExpression($commandText));
                $gamertagSingle = $gamertagSingleQuery->first();

                if ($gamertagSingle) {
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('EdgeCommand.searchGtSingle', [
                            'gamertag' => $gamertagSingle->gamertag_value,
                            'mention'  => $gamertagSingle->user->getMention(true),
                        ]))
                        ->disableNotification()
                        ->publish();

                    return;
                }

                /** @var UserGamertag $gamertagSimilarsQuery */
                /** @var UserGamertag $gamertagSimilar */
                $gamertagSimilarsQuery = UserGamertag::query();
                $gamertagSimilarsQuery->filterBySimilarity($commandText);
                $gamertagSimilarsQuery->orderBySimilarity($commandText);
                $gamertagSimilar = $gamertagSimilarsQuery->first();

                if ($gamertagSimilar) {
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('EdgeCommand.searchGtSimilarity', [
                            'gamertag' => $gamertagSimilar->gamertag_value,
                            'mention'  => $gamertagSimilar->user->getMention(true),
                        ]))
                        ->disableNotification()
                        ->publish();

                    return;
                }

                $botService->createMessage($update->message)
                    ->appendMessage(trans('EdgeCommand.searchGtEmpty'))
                    ->publish();

                return;
            }

            $botService->createMessage($update->message)
                ->appendMessage(trans('EdgeCommand.gtEmpty', [
                    'command' => trans('Command.commands.gtCommand'),
                ]))
                ->publish();

            return;
        }

        $messageMentions = $messageCommand->getMentions();

        /** @var User $messageMention */
        if (count($messageMentions) === 1) {
            $messageMention = array_first($messageMentions);

            if ($messageMention->exists) {
                $botService->createMessage($update->message)
                    ->appendMessage(trans('EdgeCommand.gtSingleRegistered', [
                        'gamertag' => $messageMention->gamertag->gamertag_value,
                    ]))
                    ->publish();

                return;
            }

            $botService->createMessage($update->message)
                ->appendMessage(trans('EdgeCommand.gtSingleUnregistered'))
                ->publish();

            return;
        }

        $messageBuilder = [];

        foreach ($messageMentions as $messageMention) {
            if ($messageMention->exists) {
                $messageBuilder[] = trans('EdgeCommand.gtItemRegistered', [
                    'gamertag' => $messageMention->gamertag->gamertag_value,
                    'mention'  => $messageMention->getMention(),
                ]);

                continue;
            }

            $messageBuilder[] = trans('EdgeCommand.gtItemUnregistered', [
                'mention' => $messageMention->getMention(),
            ]);
        }

        $botService->createMessage($update->message)
            ->appendMessage(implode($messageBuilder))
            ->disableNotification()
            ->publish();
    }

    /**
     * @inheritdoc
     */
    public function process(?User $user, Update $update): ?bool
    {
        $botService = BotService::getInstance();

        if ($update->message->isCommand(CommandService::COMMAND_START)) {
            /** @var CommandService $commandService */
            $commandService = MockupService::getInstance()->instance(CommandService::class);
            $botService->createMessage($update->message)
                ->appendMessage(trans('UserHome.homeWelcomeBack', [
                    'homeCommands' => $commandService->buildList($update),
                ]))
                ->publish();

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_COMMANDS)) {
            /** @var CommandService $commandService */
            $commandService = MockupService::getInstance()->instance(CommandService::class);
            $botService->createMessage($update->message)
                ->appendMessage($commandService->buildList($update))
                ->unduplicate(__CLASS__ . '@' . CommandService::COMMAND_COMMANDS)
                ->publish();

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_GT)) {
            self::commandGamertag($update);

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_RULES)) {
            $botService->createMessage($update->message)
                ->appendMessage(trans('UserRules.followIt'))
                ->publish();

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_LINKS)) {
            $botService->createMessage($update->message)
                ->appendMessage(trans('EdgeCommand.clanList'))
                ->publish();

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_ADMINS)) {
            $botService->createMessage($update->message)
                ->disableNotification()
                ->appendMessage(trans('UserRules.adminHeader', [
                    'admins' => implode(self::getAdministratorsList($botService)),
                ]))
                ->publish();

            return true;
        }

        if ($update->message->from->isAdminstrator()) {
            if ($update->message->isCommand(CommandService::COMMAND_BAN)) {
                $messageEntityBotCommand = $update->message->getCommand();

                if (!$messageEntityBotCommand || !$messageEntityBotCommand->getTextArgument()) {
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('EdgeCommand.banEmpty'))
                        ->publish();

                    return true;
                }

                /** @var UserGamertag $userGamertagQuery */
                $userGamertagQuery = UserGamertag::query();
                $userGamertagQuery->where('gamertag_value', $messageEntityBotCommand->getTextArgument());
                $userGamertag = $userGamertagQuery->first();

                if (!$userGamertag) {
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('EdgeCommand.banNotFound'))
                        ->publish();

                    return true;
                }

                $userGamertag->user->delete();

                $botService->createMessage($update->message)
                    ->appendMessage(trans('EdgeCommand.banSuccess'))
                    ->publish();

                $botService->sendSticker(env('NBOT_GROUP_ID'), 'CAADAQADBwADwvySEXi2rT98M7GIAg');
                $botService->createMessage($update->message)
                    ->setReceiver(env('NBOT_GROUP_ID'))
                    ->appendMessage(trans('UserSubscription.userLeftAdmin', [
                        'admin'    => $update->message->from->getMention(),
                        'fullname' => $userGamertag->user->getFullname(),
                        'gamertag' => $userGamertag->gamertag_value,
                    ]))
                    ->setReplica(false)
                    ->publish();

                return true;
            }

            if ($update->message->isCommand(CommandService::COMMAND_REFRESH)) {
                Artisan::call('cache:clear');

                $botService->createMessage($update->message)
                    ->setPrivate()
                    ->appendMessage(trans('EdgeCommand.systemRefreshed'))
                    ->publish();

                return true;
            }
        }

        if ($update->message->isCommand(CommandService::COMMAND_NEWS)) {
            $launchDate = new Carbon('2017-09-06 00:00:00');
            $carbonNow  = Carbon::now();
            $diffDays   = $carbonNow->diffInDays($launchDate, false);

            if ($diffDays >= 2) {
                $botService->createMessage($update->message)
                    ->appendMessage(trans('EdgeCommand.launchDays', [
                        'date' => $launchDate->format('d/m/Y'),
                        'days' => $diffDays,
                    ]))
                    ->publish();
            }
            else {
                $diffHours = Carbon::now()->diffInHours($launchDate, false);
                $isSoon    = $diffHours >= 2;

                if ($isSoon && $launchDate->isToday()) {
                    $diffHours = Carbon::now()->diffInHours($launchDate, false);

                    $botService->createMessage($update->message)
                        ->appendMessage(trans('EdgeCommand.launchToday', [
                            'hours' => $diffHours,
                        ]))
                        ->publish();
                }
                else if ($isSoon) {
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('EdgeCommand.launchHours', [
                            'date'  => $launchDate->format('d/m/Y'),
                            'hours' => $diffHours,
                        ]))
                        ->publish();
                }
                else if ($diffHours >= 0) {
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('EdgeCommand.launchSoon'))
                        ->publish();
                }
                else {
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('EdgeCommand.launched'))
                        ->publish();
                }
            }

            return true;
        }

        if ($update->message &&
            $update->message->isPrivate()) {
            /** @var CommandService $commandService */
            $commandService = MockupService::getInstance()->instance(CommandService::class);
            $botService->createMessage($update->message)
                ->appendMessage(trans('UserHome.commandNotSupported', [ 'homeCommands' => $commandService->buildList($update) ]))
                ->publish();

            return true;
        }

        return true;
    }
}
