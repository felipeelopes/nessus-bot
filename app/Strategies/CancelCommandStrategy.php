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
        $isCancel = $update->message->isPrivate() &&
                    strcasecmp((string) $update->message->text, trans('Command.commands.cancelCommand')) === 0;

        if ($isCancel || $update->message->isCommand()) {
            SessionService::getInstance()->clearMoment();

            if ($isCancel || $update->message->isCommand(CommandService::COMMAND_CANCEL)) {
                /** @var CommandService $commandService */
                $commandService = MockupService::getInstance()->instance(CommandService::class);
                BotService::getInstance()->createMessage($update->message)
                    ->setPrivate()
                    ->appendMessage(trans('UserHome.cancelHeader', [
                        'homeCommands' => $commandService->buildList($update),
                    ]))
                    ->publish();

                return true;
            }
        }

        return null;
    }
}
