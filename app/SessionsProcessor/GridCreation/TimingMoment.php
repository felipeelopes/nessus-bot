<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridCreation;

use Application\Adapters\Telegram\Update;
use Application\Services\Assertions\EventService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\Types\Process;
use Carbon\Carbon;

class TimingMoment extends SessionMoment
{
    public const EVENT_INVALID_FORMAT      = 'invalidFormat';
    public const EVENT_INVALID_TIMING      = 'invalidTiming';
    public const EVENT_INVALID_TOO_CLOSEST = 'invalidTooClosest';
    public const EVENT_REQUEST             = 'request';
    public const EVENT_TIMING_TODAY        = 'timingToday';
    public const EVENT_TIMING_TOMORROW     = 'timingTomorrow';

    public const PROCESS_TIMING      = 'timing';
    public const PROCESS_TIMING_TEXT = 'timingText';

    /**
     * @inheritdoc
     */
    public static function processTiming(Update $update, Process $process): ?string
    {
        $botService = BotService::getInstance();
        $format     = trim(preg_replace('/\D+/', ' ', $update->message->text));

        if ($format === '' || substr_count($format, ' ') > 1) {
            $botService->sendCancelableMessage(
                $update->message->from->id,
                trans('GridCreation.errorTimingInvalid')
            );

            assert(EventService::getInstance()->register(self::EVENT_INVALID_FORMAT));

            return self::class;
        }

        if (strpos($format, ' ') === false) {
            $format .= ' 00';
        }

        [ $timingHour, $timingMinutes ] = array_pad(array_map('intval', explode(' ', $format)), 2, 0);

        if ($timingHour === 24 && $timingMinutes === 0) {
            $timingHour = 0;
        }

        if ($timingHour > 23 || $timingMinutes > 59) {
            $botService->sendCancelableMessage(
                $update->message->from->id,
                trans('GridCreation.errorTimingInvalid')
            );

            assert(EventService::getInstance()->register(self::EVENT_INVALID_TIMING));

            return self::class;
        }

        $timingNow    = Carbon::now()->second(0);
        $timingCarbon = $timingNow->copy()->setTime($timingHour, $timingMinutes);
        $timingDiff   = $timingNow->diffInSeconds($timingCarbon, false);

        if ($timingDiff >= 0 && $timingDiff < 600) {
            $botService->sendCancelableMessage(
                $update->message->from->id,
                trans('GridCreation.errorTimingTooShort')
            );

            assert(EventService::getInstance()->register(self::EVENT_INVALID_TOO_CLOSEST));

            return self::class;
        }

        $timingToday = $timingDiff > 0;
        $timingText  = null;

        if ($timingToday) {
            $timingText = trans('GridCreation.creationWizardTimingConfirmToday', [
                'timing' => $timingCarbon->format('H:i'),
            ]);

            assert(EventService::getInstance()->register(self::EVENT_TIMING_TODAY));
        }
        else {
            $timingCarbon->addDay();
            $timingText = trans('GridCreation.creationWizardTimingConfirmTomorrow', [
                'day'    => $timingCarbon->format('d/m'),
                'timing' => $timingCarbon->format('H:i'),
            ]);

            assert(EventService::getInstance()->register(self::EVENT_TIMING_TOMORROW));
        }

        $process->put(self::PROCESS_TIMING, $timingCarbon);
        $process->put(self::PROCESS_TIMING_TEXT, $timingText);

        return null;
    }

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        $botService = BotService::getInstance();
        $botService->sendCancelableMessage(
            $update->message->from->id,
            trans('GridCreation.creationWizardTiming')
        );

        assert(EventService::getInstance()->register(self::EVENT_REQUEST));
    }

    /**
     * @inheritdoc
     */
    public function validateInput(string $input, Update $update, Process $process): ?string
    {
        $validateTiming = self::processTiming($update, $process);

        if ($validateTiming !== null) {
            return $validateTiming;
        }

        return TimingConfirmMoment::class;
    }
}
