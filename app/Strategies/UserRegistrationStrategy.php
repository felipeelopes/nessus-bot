<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Services\SessionService;
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
        /** @var UserService $userService */
        $userService = app(UserService::class);
        $user        = $userService->get($update->message->from->id);

        if ($user === null) {
            SessionService::getInstance()->initializeProcessor(UserRegistrationSessionProcessor::class, $update);
        }

        return null;
    }
}
