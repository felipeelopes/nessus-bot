<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridCreation;

use Application\Adapters\Grid;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid as GridModel;
use Application\Models\GridSubscription;
use Application\Services\Assertions\EventService;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\Types\Process;

class ConfirmMoment extends SessionMoment
{
    public const EVENT_INVALID_CONFIRMATION = 'invalidConfirmation';
    public const EVENT_REQUEST              = 'request';
    public const EVENT_SAVE                 = 'save';

    private const PROCESS_GRID = 'grid';

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        $processGrid               = new Grid;
        $processGrid->title        = $process->get(TitleMoment::PROCESS_TITLE);
        $processGrid->subtitle     = $process->get(SubtitleMoment::PROCESS_SUBTITLE);
        $processGrid->requirements = $process->get(RequirementsMoment::PROCESS_REQUIREMENTS);
        $processGrid->players      = $process->get(PlayersMoment::PROCESS_PLAYERS);
        $processGrid->timing       = $process->get(TimingMoment::PROCESS_TIMING);
        $processGrid->duration     = $process->get(DurationMoment::PROCESS_DURATION);
        $processGrid->owner        = $update->message->from;

        $process->offsetSet(self::PROCESS_GRID, $processGrid);

        $botService = BotService::getInstance();
        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridCreation.creationWizardConfirmCreationHeader', [
                'structure' => $processGrid->getStructure(Grid::STRUCTURE_TYPE_EXAMPLE),
            ]),
            PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardConfirmCreationOptions'))
        );

        assert(EventService::getInstance()->register(self::EVENT_REQUEST));
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        $user = UserService::getInstance()->get($update->message->from->id);

        /** @var Grid $processGrid */
        $processGrid = $process->offsetGet(self::PROCESS_GRID);

        $grid                    = new GridModel;
        $grid->grid_title        = $processGrid->title;
        $grid->grid_subtitle     = $processGrid->subtitle;
        $grid->grid_requirements = $processGrid->requirements;
        $grid->grid_players      = $processGrid->players;
        $grid->grid_timing       = $processGrid->timing;
        $grid->grid_duration     = DurationMoment::parseDuration($processGrid->duration);
        $grid->save();

        $gridSubscription                    = new GridSubscription;
        $gridSubscription->grid_id           = $grid->id;
        $gridSubscription->gamertag_id       = $user->gamertag->id;
        $gridSubscription->subscription_rule = GridSubscription::RULE_OWNER;
        $gridSubscription->save();

        $processGrid->grid_id = $grid->id;

        $botService = BotService::getInstance();
        $botService->sendPublicMessage($processGrid->getStructure(Grid::STRUCTURE_TYPE_FULL));
        $botService->sendMessage(
            $update->message->from->id,
            trans('GridCreation.creationWizardPublished')
        );

        assert(EventService::getInstance()->register(self::EVENT_SAVE));

        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        $botService = BotService::getInstance();

        if ($input !== trans('GridCreation.creationWizardConfirmCreationYes')) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridCreation.errorPublishInvalid'),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardConfirmCreationOptions'))
            );

            assert(EventService::getInstance()->register(self::EVENT_INVALID_CONFIRMATION));

            return self::class;
        }

        return null;
    }
}
