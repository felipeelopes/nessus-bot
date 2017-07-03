<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\User;
use Application\Services\CommandService;
use Application\Services\MockupService;
use Application\Services\SessionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\UserRegistrationSessionProcessor;
use Application\Strategies\Contracts\UserStrategyContract;

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
            $botService->sendMessage(
                $update->message->chat->id,
                trans('UserRules.followIt')
            );

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

        if ($user === null) {
            SessionService::getInstance()->initializeProcessor(UserRegistrationSessionProcessor::class, $update);
        }

        return true;
    }
}
