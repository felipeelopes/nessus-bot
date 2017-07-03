<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\User;
use Application\Services\SessionService;
use Application\SessionsProcessor\GridSubscriptionSessionProcessor;
use Application\Strategies\Contracts\UserStrategyContract;

class GridSubscriptionStrategy implements UserStrategyContract
{
    /**
     * @inheritdoc
     */
    public function process(?User $user, Update $update): ?bool
    {
        if ($user === null) {
            return null;
        }

        $sessionService  = SessionService::getInstance();
        $serviceResponse = $sessionService->initializeProcessor(GridSubscriptionSessionProcessor::class, $update);

        if ($serviceResponse === GridSubscriptionSessionProcessor::class . '@' . GridSubscriptionSessionProcessor::MOMENT_ACCEPTED) {
            $sessionService->setMoment(null);

            return true;
        }

        return $serviceResponse !== null;
    }
}
