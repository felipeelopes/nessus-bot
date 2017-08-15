<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Grid as GridAdapter;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridCreation\TimingMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;
use Carbon\Carbon;

class ModifyTimingMoment extends SessionMoment
{
    use ModificationMoment;

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        $gridAdapter = GridAdapter::fromModel($grid);

        $botService = BotService::getInstance();
        $botService->sendCancelableMessage(
            $update->message->from->id,
            trans('GridModification.modifyTimingWizard', [
                'current' => $gridAdapter->getTimingFormatted(),
            ])
        );
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid              = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));
        $grid->grid_timing = $process->get(TimingMoment::PROCESS_TIMING);
        $grid->save();

        $gridAdapter = GridAdapter::fromModel($grid);

        static::notifyUpdate($update, $process, trans('GridModification.modifyTimingUpdated', [
            'value' => $gridAdapter->getTimingFormatted(),
        ]));

        return InitializationMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        /** @var Carbon $timingCarbon */
        $inputParsed = TimingMoment::parseInput($input, $timingCarbon);

        switch ($inputParsed) {
            case TimingMoment::ERROR_INVALID_FORMAT:
                $botService = BotService::getInstance();
                $botService->sendCancelableMessage(
                    $update->message->from->id,
                    trans('GridModification.errorTimingInvalid')
                );

                return self::class;
                break;
            case TimingMoment::ERROR_INVALID_TIMING:
                $botService = BotService::getInstance();
                $botService->sendCancelableMessage(
                    $update->message->from->id,
                    trans('GridModification.errorTimingInvalid')
                );

                return self::class;
                break;
            case TimingMoment::ERROR_TOO_CLOSEST:
                $botService = BotService::getInstance();
                $botService->sendCancelableMessage(
                    $update->message->from->id,
                    trans('GridModification.errorTimingTooShort')
                );

                return self::class;
                break;
        }

        $process->put(TimingMoment::PROCESS_TIMING, $timingCarbon);

        return null;
    }
}
