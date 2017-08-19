<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;

class UnsubscribeCancelMoment extends SessionMoment
{
    use ModificationMoment;

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        $grid->grid_status         = Grid::STATUS_CANCELED;
        $grid->grid_status_details = $input;
        $grid->save();

        static::notifyUpdate($update, $process, trans('GridModification.unsubscribeCancelUpdate'));

        return InitializationMoment::class;
    }
}
