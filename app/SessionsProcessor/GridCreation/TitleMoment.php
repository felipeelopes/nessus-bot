<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridCreation;

use Application\Adapters\Telegram\Update;
use Application\Services\Assertions\EventService;
use Application\Services\CommandService;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\Types\Process;

class TitleMoment extends SessionMoment
{
    public const  EVENT_LONG_RESPONSE = 'longResponse';
    public const  EVENT_REQUEST       = 'request';
    public const  EVENT_SAVE          = 'save';

    private const MAX_TITLE = 80;

    public const  PROCESS_TITLE = 'title';

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        $botService = BotService::getInstance();

        $botService->notifyPrivateMessage($update->message);
        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridCreation.creationWizard'),
            PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardOptions'))
        );

        assert(EventService::getInstance()->register(self::EVENT_REQUEST));
    }

    /**
     * @inheritdoc
     */
    public function save(string $input, Update $update, Process $process): ?string
    {
        $process->put(self::PROCESS_TITLE, $input);

        assert(EventService::getInstance()->register(self::EVENT_SAVE));

        return SubtitleMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInitialization(Update $update, Process $process): bool
    {
        return $update->message->isCommand(CommandService::COMMAND_NEW_GRID);
    }

    /**
     * @inheritdoc
     */
    public function validateInput(string $input, Update $update, Process $process): ?string
    {
        $botService = BotService::getInstance();
        $message    = $update->message->text;

        if (strlen($message) > self::MAX_TITLE) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridCreation.errorTitleTooLong', [ 'max' => self::MAX_TITLE ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardOptions'))
            );

            assert(EventService::getInstance()->register(self::EVENT_LONG_RESPONSE));

            return self::class;
        }

        return null;
    }

}
