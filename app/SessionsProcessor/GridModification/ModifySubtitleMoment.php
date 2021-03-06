<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\GridNotificationService;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridCreation\SubtitleMoment;
use Application\Types\Process;

class ModifySubtitleMoment extends SessionMoment
{
    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        $botService = BotService::getInstance();
        $botService->createMessage($update->message)
            ->setCancelable()
            ->appendMessage(trans('GridModification.modifySubtitleWizard', [
                'current' => $grid->grid_subtitle ?: trans('GridModification.modifySubtitleNone'),
            ]))
            ->setOptions(PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardSubtitleOptions')))
            ->publish();
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid                = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));
        $grid->grid_subtitle = $input;
        $grid->save();

        GridNotificationService::getInstance()
            ->notifyUpdate($update, $grid, trans('GridModification.modifySubtitleUpdated', [
                'value' => $input ?: trans('GridModification.modifySubtitleNone'),
            ]));

        return InitializationMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        if (SubtitleMoment::inputMaxLengthValidation($input)) {
            $botService = BotService::getInstance();
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridModification.errorSubtitleTooLong', [
                    'max' => SubtitleMoment::MAX_SUBTITLE,
                ]))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardSubtitleOptions')))
                ->publish();

            return self::class;
        }

        return null;
    }
}
