<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Grid as GridAdapter;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridCreation\DurationMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;

class ModifyDurationMoment extends SessionMoment
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
        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridModification.modifyDurationWizard', [
                'current' => $gridAdapter->getDurationFormatted(),
            ]),
            PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardDurationOptions'))
        );
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid                = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));
        $grid->grid_duration = DurationMoment::parseDuration($input);
        $grid->save();

        $gridAdapter = GridAdapter::fromModel($grid);

        static::notifyUpdate($update, $process, trans('GridModification.modifyDurationUpdated', [
            'value' => $gridAdapter->getDurationFormatted(),
        ]));

        return InitializationMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        if (!is_numeric($input)) {
            $botService = BotService::getInstance();
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridModification.errorDurationInvalid'),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardDurationOptions'))
            );

            return self::class;
        }

        return null;
    }
}
