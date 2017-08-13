<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridCreation\TitleMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;

class ModifyTitleMoment extends SessionMoment
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
            trans('GridModification.modifyTitleWizard', [
                'current' => $grid->grid_title,
            ]),
            PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardOptions'))
        );
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid             = $process->get(InitializationMoment::PROCESS_GRID);
        $grid->grid_title = $input;
        $grid->save();

        static::notifyUpdate($update, $process, trans('GridModification.modifyTitleUpdated', [
            'value' => $input,
        ]));

        return InitializationMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        if (TitleMoment::inputMaxLengthValidation($input)) {
            $botService = BotService::getInstance();
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridModification.errorTitleTooLong', [ 'max' => TitleMoment::MAX_TITLE ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardOptions'))
            );

            return self::class;
        }

        return null;
    }
}
