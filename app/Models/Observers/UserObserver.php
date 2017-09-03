<?php

declare(strict_types = 1);

namespace Application\Models\Observers;

use Application\Models\Model;
use Application\Models\User;
use Application\Models\UserGamertag;

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
