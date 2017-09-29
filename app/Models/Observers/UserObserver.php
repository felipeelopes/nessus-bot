<?php

declare(strict_types = 1);

namespace Application\Models\Observers;

use Application\Models\Model;
use Application\Models\Stat;
use Application\Models\User;
use Application\Models\UserGamertag;
use Application\Services\Telegram\BotService;

class UserObserver extends Observer
{
    /**
     * @inheritdoc
     * @param User $model User instance.
     */
    public function deleting(Model $model): void
    {
        if ($model->gamertag) {
            $model->gamertag->delete();
        }

        self::deleteSettings($model);

        BotService::getInstance()->banUser($model);

        /** @var Stat $stats */
        $statsQuery = Stat::query();
        $statsQuery->where('user_id', $model->id);
        $stats = $statsQuery->get();

        foreach ($stats as $stat) {
            $stat->delete();
        }
    }

    /**
     * @inheritdoc
     * @param User $model User instance.
     */
    public function restoring(Model $model): void
    {
        /** @var UserGamertag $userGamertagRelation */
        $userGamertagRelation = $model->gamertag();
        $userGamertagRelation = $userGamertagRelation->withTrashed()->first();
        $userGamertagRelation->restore();
    }
}
