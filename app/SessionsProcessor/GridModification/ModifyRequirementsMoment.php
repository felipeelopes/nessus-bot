<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridCreation\RequirementsMoment;
use Application\SessionsProcessor\GridCreation\SubtitleMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;

class ModifyRequirementsMoment extends SessionMoment
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
            trans('GridModification.modifyRequirementsWizard', [
                'max'     => RequirementsMoment::MAX_REQUIREMENTS,
                'current' => $grid->grid_requirements ?: trans('GridModification.modifyRequirementsNone'),
            ]),
            PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardRequirementsOptions'))
        );
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid                    = $process->get(InitializationMoment::PROCESS_GRID);
        $grid->grid_requirements = $input;
        $grid->save();

        static::notifyUpdate($update, $process, trans('GridModification.modifyRequirementsUpdated', [
            'value' => $input ?: trans('GridModification.modifyRequirementsNone'),
        ]));

        return InitializationMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        if (RequirementsMoment::inputMaxLengthValidation($input)) {
            $botService = BotService::getInstance();
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridModification.errorRequirementsTooLong', [ 'max' => SubtitleMoment::MAX_SUBTITLE ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardRequirementsOptions'))
            );

            return self::class;
        }

        return null;
    }
}
