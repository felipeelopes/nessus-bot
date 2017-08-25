<?php

declare(strict_types = 1);

namespace Application\Models\Observers;

use Application\Models\Grid;
use Application\Models\GridSubscription;
use Application\Models\Model;
use Application\Models\UserGamertag;
use Illuminate\Database\Eloquent\Builder;

class UserGamertagObserver extends Observer
{
    /**
     * @inheritdoc
     * @param UserGamertag $model User instance.
     */
    public function deleting(Model $model): void
    {
        /** @var Grid|Builder $gridsQuery */
        $gridsQuery = Grid::query();
        $gridsQuery->filterAvailables();
        $gridsQuery->filterOwneds($model->user);

        /** @var int[] $gridsIds */
        $gridsIds = $gridsQuery->pluck('id');

        /** @var GridSubscription $gridSubscriptionsQuery */
        $gridSubscriptionsQuery = GridSubscription::query();
        $gridSubscriptionsQuery->whereIn('grid_id', $gridsIds);
        $gridSubscriptionsQuery->where('gamertag_id', $model->id);

        /** @var GridSubscription $gridSubscriptions */
        $gridSubscriptions = $gridSubscriptionsQuery->get([ 'id' ]);

        foreach ($gridSubscriptions as $gridSubscription) {
            $gridSubscription->delete();
        }
    }
}
