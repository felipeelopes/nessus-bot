<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridCreation\PlayersMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;

class ModifyPlayersMoment extends SessionMoment
{
    use ModificationMoment;

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
            ->appendMessage(trans('GridModification.modifyPlayersWizard', [
                'max'     => PlayersMoment::MAX_PLAYERS,
                'current' => $grid->grid_players,
            ]))
            ->setOptions(PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardPlayersOptions')))
            ->publish();
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid               = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));
        $grid->grid_players = $input;
        $grid->save();

        static::notifyUpdate($update, $process, trans('GridModification.modifyPlayersUpdated', [
            'value' => $input,
        ]));

        return InitializationMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        $playersCount = (int) $input;

        if (!$playersCount) {
            $botService = BotService::getInstance();
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridModification.errorPlayersInvalid', [
                    'max' => PlayersMoment::MAX_PLAYERS,
                ]))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardPlayersOptions')))
                ->publish();

            return self::class;
        }

        if ($playersCount < 2) {
            $botService = BotService::getInstance();
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridModification.errorPlayersTooFew', [ 'max' => PlayersMoment::MAX_PLAYERS ]))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardPlayersOptions')))
                ->publish();

            return self::class;
        }

        if ($playersCount > PlayersMoment::MAX_PLAYERS) {
            $botService = BotService::getInstance();
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridModification.errorPlayersTooMuch', [ 'max' => PlayersMoment::MAX_PLAYERS ]))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardPlayersOptions')))
                ->publish();

            return self::class;
        }

        return null;
    }
}
