<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridCreation;

use Application\Adapters\Telegram\Update;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\Types\Process;

class SubtitleMoment extends SessionMoment
{
    private const MAX_SUBTITLE     = 20;
    public const  PROCESS_SUBTITLE = 'subtitle';

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        $botService = BotService::getInstance();
        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridCreation.creationWizardSubtitle'),
            PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardSubtitleOptions'))
        );
    }

    /**
     * @inheritdoc
     */
    public function save(string $input, Update $update, Process $process): ?string
    {
        $process->put(self::PROCESS_SUBTITLE, $input);

        return RequirementsMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(string $input, Update $update, Process $process): ?string
    {
        $botService = BotService::getInstance();
        $message    = $update->message->text;

        if (strlen($message) > self::MAX_SUBTITLE) {
            $botService->sendPredefinedMessage(
                $update->message->from->id,
                trans('GridCreation.errorSubtitleTooLong', [ 'max' => self::MAX_SUBTITLE ]),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardSubtitleOptions'))
            );

            return self::class;
        }

        return null;
    }
}
