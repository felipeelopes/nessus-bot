<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridCreation;

use Application\Adapters\Telegram\Update;
use Application\Services\Assertions\EventService;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\Types\Process;
use Carbon\Carbon;

class DurationMoment extends SessionMoment
{
    public const EVENT_INVALID = 'invalid';
    public const EVENT_REQUEST = 'request';
    public const EVENT_SAVE    = 'save';

    public const PROCESS_DURATION = 'duration';

    /**
     * Parse duration to Carbon.
     * @param int|null $duration Duration input.
     * @return Carbon
     */
    public static function parseDuration(?int $duration): Carbon
    {
        return Carbon::createFromTime((int) $duration, round(fmod((float) $duration, 1) * 60), 0);
    }

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        $botService = BotService::getInstance();
        $botService->sendPredefinedMessage(
            $update->message->from->id,
            trans('GridCreation.creationWizardDuration'),
            PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardDurationOptions'))
        );

        assert(EventService::getInstance()->register(self::EVENT_REQUEST));
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        $process->put(self::PROCESS_DURATION, (int) $input);

        assert(EventService::getInstance()->register(self::EVENT_SAVE));

        return PlayersMoment::class;
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
                trans('GridCreation.errorDurationInvalid'),
                PredefinitionService::getInstance()->optionsFrom(trans('GridCreation.creationWizardDurationOptions'))
            );

            assert(EventService::getInstance()->register(self::EVENT_INVALID));

            return self::class;
        }

        return null;
    }
}
