<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridCreation\SubtitleMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;

class ModifySubtitleMoment extends SessionMoment
{
    use ModificationMoment;

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        /** @var Grid $grid */
        $grid = $process->get(InitializationMoment::PROCESS_GRID);

        $botService = BotService::getInstance();
        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridModification.modifySubtitleWizard', [
                'current' => $grid->grid_subtitle ?: trans('GridModification.modifySubtitleNone'),
            ]),
            PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardSubtitleOptions'))
        );
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid                = $process->get(InitializationMoment::PROCESS_GRID);
        $grid->grid_subtitle = $input;
        $grid->save();

        static::notifyUpdate($update, $process, trans('GridModification.modifySubtitleUpdated', [
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
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridModification.errorSubtitleTooLong', [ 'max' => SubtitleMoment::MAX_SUBTITLE ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardSubtitleOptions'))
            );

            return self::class;
        }

        return null;
    }
}
