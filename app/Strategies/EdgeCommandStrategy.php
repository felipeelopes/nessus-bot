<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\User;
use Application\Services\CommandService;
use Application\Services\MockupService;
use Application\Services\Telegram\BotService;
use Application\Strategies\Contracts\UserStrategyContract;
use Carbon\Carbon;

class EdgeCommandStrategy implements UserStrategyContract
{
    /**
     * @inheritdoc
     */
    public function process(?User $user, Update $update): ?bool
    {
        $botService = BotService::getInstance();

        if ($update->message->isCommand(CommandService::COMMAND_START)) {
            /** @var CommandService $commandService */
            $commandService = MockupService::getInstance()->instance(CommandService::class);
            $botService->sendMessage(
                $update->message->chat->id,
                trans('UserHome.homeWelcomeBack', [ 'homeCommands' => $commandService->buildList($user) ])
            );

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_COMMANDS)) {
            /** @var CommandService $commandService */
            $commandService = MockupService::getInstance()->instance(CommandService::class);
            $botService->notifyPrivateMessage($update->message);
            $botService->sendMessage(
                $update->message->from->id,
                $commandService->buildList($user)
            );

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_RULES)) {
            $chatMembers = $botService->getChatAdministrators();
            $admins      = [];

            foreach ($chatMembers as $chatMember) {
                if ($chatMember->user->id === (int) env('NBOT_WEBHOOK_ID')) {
                    continue;
                }

                $admins[] = $chatMember->user->getMention();
            }

            sort($admins);

            $botService->sendMessage(
                $update->message->chat->id,
                trans('UserRules.followIt') .
                trans('UserRules.adminHeader', [
                    'admins' => implode(array_map(function ($userMention) {
                        return trans('UserRules.adminItem', [
                            'username' => $userMention,
                        ]);
                    }, $admins)),
                ])
            );

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_NEWS)) {
            $launchDate = new Carbon('2017-09-06 00:00:00');
            $carbonNow  = Carbon::now();
            $diffDays   = $carbonNow->diffInDays($launchDate, false);

            if ($diffDays >= 2) {
                $botService->sendMessage(
                    $update->message->chat->id,
                    trans('EdgeCommand.launchDays', [
                        'date' => $launchDate->format('d/m/Y'),
                        'days' => $diffDays,
                    ])
                );
            }
            else {
                $diffHours = Carbon::now()->diffInHours($launchDate, false);
                $isSoon    = $diffHours >= 2;

                if ($isSoon && $launchDate->isToday()) {
                    $diffHours = Carbon::now()->diffInHours($launchDate, false);

                    $botService->sendMessage(
                        $update->message->chat->id,
                        trans('EdgeCommand.launchToday', [
                            'hours' => $diffHours,
                        ])
                    );
                }
                else if ($isSoon) {
                    $botService->sendMessage(
                        $update->message->chat->id,
                        trans('EdgeCommand.launchHours', [
                            'date'  => $launchDate->format('d/m/Y'),
                            'hours' => $diffHours,
                        ])
                    );
                }
                else if ($diffHours >= 0) {
                    $botService->sendMessage(
                        $update->message->chat->id,
                        trans('EdgeCommand.launchSoon')
                    );
                }
                else {
                    $botService->sendMessage(
                        $update->message->chat->id,
                        trans('EdgeCommand.launched')
                    );
                }
            }

            return true;
        }

        if ($update->message &&
            $update->message->isPrivate()) {
            /** @var CommandService $commandService */
            $commandService = MockupService::getInstance()->instance(CommandService::class);
            $botService->sendMessage(
                $update->message->chat->id,
                trans('UserHome.commandNotSupported', [ 'homeCommands' => $commandService->buildList($user) ])
            );

            return true;
        }

        return true;
    }
}
