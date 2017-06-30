<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Services\SessionService;
use Application\Services\Telegram\BotService;
use Application\Strategies\Contracts\UpdateStrategyContract;

class CancelCommandStrategy implements UpdateStrategyContract
{
    /**
     * @inheritdoc
     */
    public function process(Update $update): ?bool
    {
        if ($update->callback_query !== null &&
            $update->callback_query->data === BotService::QUERY_CANCEL) {
            SessionService::getInstance()->setMoment(null);

            BotService::getInstance()->deleteReplyMarkup($update->callback_query->message);
            BotService::getInstance()->sendMessage(
                $update->callback_query->message->chat->id,
                trans('UserHome.cancelHeader', [ 'homeCommands' => trans('UserHome.homeCommands'), ])
            );

            return true;
        }

        return null;
    }
}
