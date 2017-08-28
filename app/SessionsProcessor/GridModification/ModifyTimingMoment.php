<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Grid as GridAdapter;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\GridNotificationService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridCreation\TimingMoment;
use Application\Types\Process;
use Carbon\Carbon;

class ModifyTimingMoment extends SessionMoment
{
    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        $gridAdapter = GridAdapter::fromModel($grid);

        $botService = BotService::getInstance();
        $botService->createMessage($update->message)
            ->setCancelable()
            ->appendMessage(trans('GridModification.modifyTimingWizard', [
                'current' => $gridAdapter->getTiming(),
            ]))
            ->publish();
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

        GridNotificationService::getInstance()
            ->notifyUpdate($update, $grid, trans('GridModification.modifyTimingUpdated', [
                'value' => $gridAdapter->getTiming(),
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
                $botService->createMessage($update->message)
                    ->setCancelable()
                    ->appendMessage(trans('GridModification.errorTimingInvalid'))
                    ->publish();

                return self::class;
                break;
            case TimingMoment::ERROR_INVALID_TIMING:
                $botService = BotService::getInstance();
                $botService->createMessage($update->message)
                    ->setCancelable()
                    ->appendMessage(trans('GridModification.errorTimingInvalid'))
                    ->publish();

                return self::class;
                break;
            case TimingMoment::ERROR_TOO_CLOSEST:
                $botService = BotService::getInstance();
                $botService->createMessage($update->message)
                    ->setCancelable()
                    ->appendMessage(trans('GridModification.errorTimingTooShort'))
                    ->publish();

                return self::class;
                break;
        }

        $process->put(TimingMoment::PROCESS_TIMING, $timingCarbon);

        return null;
    }
}
