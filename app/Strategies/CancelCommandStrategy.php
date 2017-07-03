<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\User;
use Application\Services\CommandService;
use Application\Services\MockupService;
use Application\Services\SessionService;
use Application\Services\Telegram\BotService;
use Application\Strategies\Contracts\UserStrategyContract;

class CancelCommandStrategy implements UserStrategyContract
{
    /**
     * @inheritdoc
     */
    public function process(?User $user, Update $update): ?bool
    {
        if ($update->message->isCommand()) {
            SessionService::getInstance()->setMoment(null);

            if ($update->message->isCommand(CommandService::COMMAND_CANCEL)) {
                /** @var CommandService $commandService */
                $commandService = MockupService::getInstance()->instance(CommandService::class);
                BotService::getInstance()->sendMessage(
                    $update->message->chat->id,
                    trans('UserHome.cancelHeader', [ 'homeCommands' => $commandService->buildList($user) ])
                );

                return true;
            }
        }

        return null;
    }
}
