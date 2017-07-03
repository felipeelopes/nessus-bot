<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\User;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\Strategies\Contracts\UserStrategyContract;

class PredefinitionStrategy implements UserStrategyContract
{
    /**
     * @inheritdoc
     */
    public function process(?User $user, Update $update): ?bool
    {
        if ($update->message->isCommand()) {
            $command = substr($update->message->getCommand(), 1);

            if (!ctype_digit($command)) {
                return null;
            }

            $predefinitions = PredefinitionService::getInstance()->getOptions();

            foreach ($predefinitions as $predefinition) {
                if ($predefinition->command === $command) {
                    $update->message->text     = (string) $predefinition->value;
                    $update->message->entities = null;

                    return null;
                }
            }

            $botService = BotService::getInstance();
            $botService->sendMessage(
                $update->message->chat->id,
                trans('Predefinition.errorNotFound')
            );

            return true;
        }

        return null;
    }
}
