<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Predefinition\OptionItem;
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
        $predefinitions = PredefinitionService::getInstance()->getOptions();
        $message        = (string) $update->message->text;

        /** @var OptionItem|null $commandMatch */
        $commandMatch = array_first($predefinitions, function (OptionItem $optionItem) use ($message) {
            return $optionItem->command === $message ||
                   ($optionItem->value && strcasecmp((string) $optionItem->value, $message) === 0);
        });

        if ($commandMatch !== null) {
            $update->message->text = (string) $commandMatch->value;

            return null;
        }

        if ($update->message->isCommand()) {
            $command = substr($update->message->getCommand()->command, 1);

            foreach ($predefinitions as $predefinition) {
                if (($predefinition->command === $command && isset($predefinition->value)) ||
                    ($predefinition->value && strcasecmp((string) $predefinition->value, $command) === 0)) {
                    $update->message->text     = (string) $predefinition->value;
                    $update->message->entities = null;

                    return null;
                }
            }

            if (!ctype_digit($command)) {
                return null;
            }

            BotService::getInstance()->createMessage($update->message)
                ->appendMessage(trans('Predefinition.errorNotFound'))
                ->publish();

            return true;
        }

        return null;
    }
}
