<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\GridNotificationService;
use Carbon\Carbon;

class GridPlayingExecutor extends Executor
{
    /**
     * @inheritdoc
     */
    public function run(): ?bool
    {
        /** @var Grid $gridsQuery */
        $gridsQuery = Grid::query();
        $gridsQuery->filterAvailables();
        $gridsQuery->where('grid_status', '!=', Grid::STATUS_PLAYING);
        $gridsQuery->where('grid_timing', '<=', Carbon::now());
        $grids = $gridsQuery->get();

        /** @var Grid $grid */
        foreach ($grids as $grid) {
            $grid->grid_status = Grid::STATUS_PLAYING;
            $grid->save();

            $update = new Update([ 'message' => [ 'from' => [] ] ]);
            $update->message->forcePublic();

            GridNotificationService::getInstance()
                ->notifyUpdate($update, $grid);
        }

        return true;
    }
}
