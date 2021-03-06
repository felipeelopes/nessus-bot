<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\GridNotificationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GridRespawnExecutor extends Executor
{
    public const LAST_RESPAWN_REFERENCE = 'lastRespawnReference';

    /**
     * @inheritdoc
     */
    public function run(): ?bool
    {
        /** @var Grid $gridsQuery */
        $gridsQuery = Grid::query();
        $gridsQuery->filterOpeneds();
        $gridsQuery->where(function (Builder $builder) {
            $builder->orWhere(function (Builder $builder) {
                /** @var Grid $builder */
                $builder->filterMinutesDifference(240);
                $builder->filterLastTouchBefore(self::LAST_RESPAWN_REFERENCE, Carbon::now()->subHour());
            });
            $builder->orWhere(function (Builder $builder) {
                /** @var Grid $builder */
                $builder->filterMinutesDifference(60, 240);
                $builder->filterLastTouchBefore(self::LAST_RESPAWN_REFERENCE, Carbon::now()->subMinute(30));
            });
            $builder->orWhere(function (Builder $builder) {
                /** @var Grid $builder */
                $builder->filterMinutesDifference(15, 60);
                $builder->filterLastTouchBefore(self::LAST_RESPAWN_REFERENCE, Carbon::now()->subMinute(15));
            });
            $builder->orWhere(function (Builder $builder) {
                /** @var Grid $builder */
                $builder->filterMinutesDifference(0, 15);
                $builder->filterLastTouchBefore(self::LAST_RESPAWN_REFERENCE, Carbon::now()->subMinute(5));
            });
            $builder->orWhere(function (Builder $builder) {
                /** @var Grid $builder */
                $builder->filterMinutesDifference(null, 0);
                $builder->filterLastTouchBefore(self::LAST_RESPAWN_REFERENCE, Carbon::now()->subMinute(15));
            });
        });

        /** @var Grid[]|Collection $grids */
        $grids = $gridsQuery->get();

        foreach ($grids as $grid) {
            $update = new Update([ 'message' => [ 'from' => [] ] ]);
            $update->message->forcePublic();

            GridNotificationService::getInstance()
                ->notifyUpdate($update, $grid);
        }

        return true;
    }
}
