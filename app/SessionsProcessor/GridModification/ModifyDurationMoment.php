<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Grid as GridAdapter;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\GridNotificationService;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridCreation\DurationMoment;
use Application\Types\Process;

class ModifyDurationMoment extends SessionMoment
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
            ->appendMessage(trans('GridModification.modifyDurationWizard', [
                'current' => $gridAdapter->getDuration(),
            ]))
            ->setOptions(PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardDurationOptions')))
            ->publish();
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid                = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));
        $grid->grid_duration = DurationMoment::parseDuration((int) $input);
        $grid->save();

        $gridAdapter = GridAdapter::fromModel($grid);

        GridNotificationService::getInstance()
            ->notifyUpdate($update, $grid, trans('GridModification.modifyDurationUpdated', [
                'value' => $gridAdapter->getDuration(),
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
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridModification.errorDurationInvalid'))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardDurationOptions')))
                ->publish();

            return self::class;
        }

        return null;
    }
}
