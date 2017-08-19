<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;

class UnsubscribeMoment extends SessionMoment
{
    use ModificationMoment;

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        $userSubscription = $grid->getUserSubscription($update->message->from);

        if ($userSubscription !== null) {
            $userSubscription->delete();
        }

        static::notifyUpdate($update, $process, trans('GridModification.unsubscribeYouUpdate'));
    }
}
