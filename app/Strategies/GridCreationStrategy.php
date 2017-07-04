<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\User;
use Application\Services\SessionService;
use Application\SessionsProcessor\GridCreation\TitleMoment;
use Application\Strategies\Contracts\UserStrategyContract;

class GridCreationStrategy implements UserStrategyContract
{
    /**
     * @inheritdoc
     */
    public function process(?User $user, Update $update): ?bool
    {
        if ($user === null) {
            return null;
        }

        $sessionService = SessionService::getInstance();
        $sessionService->setInitialMoment(TitleMoment::class);

        return $sessionService->run($update);
    }
}
