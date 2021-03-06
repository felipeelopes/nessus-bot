<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Predefinition\OptionItem;
use Application\Adapters\Ranking\PlayerRanking;
use Application\Adapters\Telegram\Update;
use Application\Events\CheckActivitiesExecutor;
use Application\Models\User;
use Application\Models\UserGamertag;
use Application\Services\CommandService;
use Application\Services\KeyboardService;
use Application\Services\MockupService;
use Application\Services\Telegram\BotService;
use Application\Services\UserExperienceService;
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
                            'clan'     => $gamertagSingle->getClan() ?? '-',
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
                            'clan'     => $gamertagSimilar->getClan() ?? '-',
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
     * Update User ranking.
     * If need, send a personal new level notification.
     * @throws \Exception
     */
    private static function updateUserRanking(User $user): void
    {
        /** @var PlayerRanking|null $userRanking */
        $userExperience = UserExperienceService::getInstance();
        $globalRanking  = $userExperience->getGlobalRanking();
        $userRanking    = $globalRanking->get($user->id);

        CheckActivitiesExecutor::processActivities($user, true);

        $userExperience->forgetGlobalRanking();

        if ($userRanking) {
            $userLevel = $userRanking->getLevel();

            /** @var PlayerRanking $updatedUserRanking */
            $updatedGlobalRanking = $userExperience->getGlobalRanking();
            $updatedUserRanking   = $updatedGlobalRanking->get($user->id);
            $updatedUserLevel     = $updatedUserRanking->getLevel();

            if ($updatedUserLevel->level > $userLevel->level) {
                BotService::getInstance()->createMessage()
                    ->appendMessage(trans('Ranking.levelAdvanced', [
                        'gamertag' => $user->gamertag->gamertag_value,
                        'level'    => $updatedUserLevel->getIconTitle(true),
                    ]))
                    ->forcePublic()
                    ->publish();
            }
        }
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

        if (!$user || !$user->exists || $user->deleted_at || !$user->gamertag) {
            return false;
        }

        if ($update->message->isCommand(CommandService::COMMAND_RANKING)) {
            BotService::getInstance()
                ->createMessage($update->message)
                ->appendMessage(trans('Stats.activitiesRequest'))
                ->unduplicate(self::class . '@Command:' . CommandService::COMMAND_RANKING . '@User:' . $user->id)
                ->publish();

            static::updateUserRanking($user);

            $messageCommand = $update->message->getCommand();
            assert($messageCommand !== null);

            BotService::getInstance()
                ->createMessage($update->message)
                ->appendMessage($this->generateRanking($user, $update->message->isAdministrative() ? 20 : 10, $messageCommand->getTextArgument()))
                ->unduplicate(self::class . '@Command:' . CommandService::COMMAND_RANKING . '@User:' . $user->id)
                ->setOptions([
                    OptionItem::fromCommand(CommandService::COMMAND_MY_RANKING),
                ])
                ->publish();

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_MY_RANKING)) {
            BotService::getInstance()
                ->createMessage($update->message)
                ->appendMessage(trans('Stats.activitiesRequest'))
                ->unduplicate(self::class . '@Command:' . CommandService::COMMAND_MY_RANKING . '@User:' . $user->id)
                ->publish();

            static::updateUserRanking($user);

            BotService::getInstance()
                ->createMessage($update->message)
                ->appendMessage($this->generateUserExperience($user))
                ->unduplicate(self::class . '@Command:' . CommandService::COMMAND_MY_RANKING . '@User:' . $user->id)
                ->setOptions([
                    OptionItem::fromCommand(CommandService::COMMAND_RANKING),
                    OptionItem::fromCommand(CommandService::COMMAND_MY_RANKING),
                ])
                ->publish();

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_GT)) {
            self::commandGamertag($update);

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

    /**
     * Generate and return the ranking message.
     * @return string
     */
    private function generateRanking(User $you, ?int $limit = null, ?string $variant = null): string
    {
        $globalRanking = UserExperienceService::getInstance()->getGlobalRanking();
        $usersIds      = $globalRanking->pluck('user_id');

        $usersQuery = User::query();
        $usersQuery->with('gamertag');
        $usersQuery->whereIn('id', $usersIds);
        $users        = $usersQuery->get()->keyBy('id');
        $usersRanking = array_fill_keys($usersIds->toArray(), 0);

        $usersRanked = $globalRanking;

        $variantExceptAdmins = $variant === 'except-admins';

        if ($variantExceptAdmins) {
            $limit = null;
        }

        if ($limit !== null) {
            $usersRanked = $usersRanked->slice(0, $limit);
        }

        $rankingContents = [];
        $rankingIndex    = 0;
        $rankingCount    = 0;
        $rankingYou      = false;

        /** @var PlayerRanking $userRank */
        foreach ($usersRanked as $userRank) {
            if ($variantExceptAdmins) {
                if ($userRank->user()->isAdminstrator()) {
                    continue;
                }

                $rankingCount++;

                if ($rankingCount > 25) {
                    break;
                }
            }

            $rankingIndex++;
            $rankingYou = $rankingYou || $userRank->user_id === $you->id;

            /** @var User $user */
            $user              = $users->get($userRank->user_id);
            $rankingContents[] = trans('Stats.rankingPointer', [
                'ranking'  => $rankingIndex,
                'gamertag' => $user->gamertag->gamertag_value,
                'icon'     => $userRank->getLevel()->getIconTitle(),
                'xp'       => number_format($userRank->player_experience, 0, '', '.'),
                'you'      => $userRank->user_id === $you->id
                    ? trans('Stats.rankingYou')
                    : null,
            ]);
        }

        if (!$variantExceptAdmins &&
            $limit !== null &&
            !$rankingYou && $users->get($you->id)) {
            $rankingIndex = array_search($you->id, array_keys($usersRanking), false) + 1;

            if ($rankingIndex !== $limit + 1) {
                $rankingContents[] = trans('Stats.rankingSeparator');
            }

            /** @var PlayerRanking $youRank */
            $youRank = $globalRanking->where('user_id', $you->id)->first();

            $rankingContents[] = trans('Stats.rankingPointer', [
                'ranking'  => $rankingIndex,
                'gamertag' => $you->gamertag->gamertag_value,
                'icon'     => $youRank->getLevel()->getIconTitle(),
                'xp'       => number_format($youRank->player_experience, 0, '', '.'),
                'you'      => trans('Stats.rankingYou'),
            ]);
        }

        return trans('Stats.rankingHeader', [
            'pointers' => implode($rankingContents),
        ]);
    }

    /**
     * Generate the User experience ranking.
     * @return string
     */
    private function generateUserExperience(User $user): string
    {
        $globalRanking = UserExperienceService::getInstance()->getGlobalRanking();

        if (!$globalRanking->has($user->id)) {
            return trans('Ranking.errorNotFound');
        }

        /** @var PlayerRanking $playerRanking */
        $playerRanking = $globalRanking->get($user->id);
        $playerLevel   = $playerRanking->getLevel();

        $barPercent = $playerLevel->getPercent();
        $barFilled  = str_repeat(trans('Ranking.barFilled'), (int) round($barPercent * 10)) .
                      str_repeat(trans('Ranking.barEmpty'), (int) round((1 - $barPercent) * 10));

        $nextExperience = $playerLevel->getNextExperience();

        return trans('Ranking.rankingGrid', [
            'gamertag'     => $user->gamertag->gamertag_value,
            'title'        => $playerLevel->getIconTitle(true),
            'bar'          => $barFilled,
            'percent'      => sprintf('%.1f%%', $barPercent * 100),
            'xp'           => number_format($playerRanking->player_experience, 0, '', '.'),
            'nextLevel'    => $nextExperience === null
                ? trans('Ranking.nextLevelLimited')
                : trans('Ranking.nextLevelRequirement', [
                    'xp' => number_format($nextExperience, 0, '', '.'),
                ]),
            'activities'   => number_format($playerRanking->player_activities, 0, '', '.'),
            'hours'        => sprintf('%.1f', $playerRanking->player_timing),
            'interactions' => number_format($playerRanking->player_interation, 0, '', '.'),
            'days'         => number_format($playerRanking->player_register, 0, '', '.'),
        ]);
    }
}
