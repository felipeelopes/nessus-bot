<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Models\Grid;
use Application\Models\Model;
use Carbon\Carbon;

class GridFinisherExecutor extends Executor
{
    /**
     * @inheritdoc
     */
    public function run(?Model $model = null): ?bool
    {
        /** @var Grid $gridsQuery */
        $gridsQuery = Grid::query();
        $gridsQuery->filterAvailables();
        $gridsQuery->whereRaw('ADDTIME(`grid_timing`, `grid_duration`) <= ?', [ Carbon::now() ]);
        $grids = $gridsQuery->get();

        /** @var Grid $grid */
        foreach ($grids as $grid) {
            $grid->grid_status = Grid::STATUS_FINISHED;
            $grid->save();
        }

        return true;
    }
}
