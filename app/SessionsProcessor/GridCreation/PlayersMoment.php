<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridCreation;

use Application\Adapters\Telegram\Update;
use Application\Services\Assertions\EventService;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\Types\Process;

class PlayersMoment extends SessionMoment
{
    public const EVENT_INVALID_COUNT        = 'invalidCount';
    public const EVENT_INVALID_FEW_PLAYERS  = 'invalidFewPlayers';
    public const EVENT_INVALID_MUCH_PLAYERS = 'invalidMuchPlayers';
    public const EVENT_REQUEST              = 'request';
    public const EVENT_SAVE                 = 'save';

    private const MAX_PLAYERS     = 12;
    public const  PROCESS_PLAYERS = 'players';

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        $botService = BotService::getInstance();
        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridCreation.creationWizardPlayers', [ 'max' => self::MAX_PLAYERS ]),
            PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardPlayersOptions'))
        );

        assert(EventService::getInstance()->register(self::EVENT_REQUEST));
    }

    /**
     * @inheritdoc
     */
    public function save(string $input, Update $update, Process $process): ?string
    {
        $process->put(self::PROCESS_PLAYERS, (int) $input);

        assert(EventService::getInstance()->register(self::EVENT_SAVE));

        return ConfirmMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(string $input, Update $update, Process $process): ?string
    {
        $botService   = BotService::getInstance();
        $playersCount = (int) $input;

        if (!$playersCount) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridCreation.errorPlayersInvalid', [ 'max' => self::MAX_PLAYERS ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardPlayersOptions'))
            );

            assert(EventService::getInstance()->register(self::EVENT_INVALID_COUNT));

            return self::class;
        }

        if ($playersCount < 2) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridCreation.errorPlayersTooFew', [ 'max' => self::MAX_PLAYERS ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardPlayersOptions'))
            );

            assert(EventService::getInstance()->register(self::EVENT_INVALID_FEW_PLAYERS));

            return self::class;
        }

        if ($playersCount > self::MAX_PLAYERS) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridCreation.errorPlayersTooMuch', [ 'max' => self::MAX_PLAYERS ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardPlayersOptions'))
            );

            assert(EventService::getInstance()->register(self::EVENT_INVALID_MUCH_PLAYERS));

            return self::class;
        }

        return null;
    }
}
