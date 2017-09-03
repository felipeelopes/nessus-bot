<?php

declare(strict_types = 1);

namespace Application\Models\Observers;

use Application\Models\GridSubscription;
use Application\Models\Model;
use Application\Services\GridNotificationService;
use Application\Services\Telegram\BotService;

class GridSubscriptionObserver extends Observer
{
    /**
     * @inheritdoc
     * @param GridSubscription $model
     */
    public function deleted(Model $model): void
    {
        $model->grid->acceptTitularReserve();

        GridNotificationService::getInstance()
            ->notifyUpdate(BotService::getInstance()->getUpdate(), $model->grid);

        self::deleteSettings($model);
    }
}
