<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Services\CommandService;
use Application\Services\SessionService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\SessionsProcessor\UserRegistrationSessionProcessor;
use Application\Strategies\Contracts\UpdateStrategyContract;

class UserRegistrationStrategy implements UpdateStrategyContract
{
    /**
     * @inheritdoc
     */
    public function process(Update $update): ?bool
    {
        if (!$update->message) {
            return null;
        }

        /** @var UserService $userService */
        $userService = app(UserService::class);
        $user        = $userService->get($update->message->from->id);

        if ($user === null) {
            $sessionService  = SessionService::getInstance();
            $serviceResponse = $sessionService->initializeProcessor(UserRegistrationSessionProcessor::class, $update);

            if ($serviceResponse === UserRegistrationSessionProcessor::MOMENT_REGISTERED) {
                $sessionService->setMoment(null);

                return true;
            }

            return $serviceResponse !== null;
        }

        if (strtolower($update->message->text) === CommandService::COMMAND_REGISTER) {
            BotService::getInstance()->sendMessage(
                $update->message->from->id,
                trans('UserRegistration.alreadyRegistered')
            );

            return true;
        }

        return null;
    }
}
