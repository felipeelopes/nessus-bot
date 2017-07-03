<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\User;
use Application\Services\CommandService;
use Application\Services\SessionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\UserRegistrationSessionProcessor;
use Application\Strategies\Contracts\UserStrategyContract;

class UserRegistrationStrategy implements UserStrategyContract
{
    /**
     * @inheritdoc
     */
    public function process(?User $user, Update $update): ?bool
    {
        if ($user === null) {
            $sessionService  = SessionService::getInstance();
            $serviceResponse = $sessionService->initializeProcessor(UserRegistrationSessionProcessor::class, $update);

            if ($serviceResponse === UserRegistrationSessionProcessor::class . '@' . UserRegistrationSessionProcessor::MOMENT_ACCEPTED) {
                $sessionService->setMoment(null);

                return true;
            }

            return $serviceResponse !== null;
        }

        if ($update->message->isCommand(CommandService::COMMAND_REGISTER)) {
            BotService::getInstance()->sendMessage(
                $update->message->from->id,
                trans('UserRegistration.alreadyRegistered')
            );

            return true;
        }

        return null;
    }
}
