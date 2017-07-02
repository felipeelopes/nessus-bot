<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Services\SessionService;
use Application\Services\Telegram\BotService;
use Application\Strategies\Contracts\UpdateStrategyContract;

class CancelCommandStrategy implements UpdateStrategyContract
{
    public const CANCEL_COMMAND = '/Cancelar';

    /**
     * @inheritdoc
     */
    public function process(Update $update): ?bool
    {
        if ($update->message->isCommand()) {
            SessionService::getInstance()->setMoment(null);

            if ($update->message->isCommand(self::CANCEL_COMMAND)) {
                BotService::getInstance()->sendMessage(
                    $update->message->chat->id,
                    trans('UserHome.cancelHeader', [ 'homeCommands' => trans('UserHome.homeCommands'), ])
                );

                return true;
            }
        }

        return null;
    }
}
