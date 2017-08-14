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
    public const ERROR_INVALID_FORMAT = 'errorInvalidFormat';
    public const ERROR_INVALID_TIMING = 'errorInvalidTiming';
    public const ERROR_TOO_CLOSEST    = 'errorTooClosest';

    public const EVENT_INVALID_FORMAT      = 'invalidFormat';
    public const EVENT_INVALID_TIMING      = 'invalidTiming';
    public const EVENT_INVALID_TOO_CLOSEST = 'invalidTooClosest';
    public const EVENT_REQUEST             = 'request';
    public const EVENT_TIMING_TODAY        = 'timingToday';
    public const EVENT_TIMING_TOMORROW     = 'timingTomorrow';

    public const PROCESS_TIMING      = 'timing';
    public const PROCESS_TIMING_TEXT = 'timingText';

    /**
     * Parse the timing input.
     * @param null|string $input        Input value.
     * @param Carbon|null $timingCarbon Carbon output.
     * @param bool|null   $timingToday  Is today output.
     * @return string|null
     */
    public static function parseInput(?string $input, ?Carbon &$timingCarbon = null, ?bool &$timingToday = null): ?string
    {
        $format = trim(preg_replace('/\D+/', ' ', $input));

        if ($format === '' || substr_count($format, ' ') > 1) {
            return self::ERROR_INVALID_FORMAT;
        }

        if (strpos($format, ' ') === false) {
            $format .= ' 00';
        }

        [ $timingHour, $timingMinutes ] = array_pad(array_map('intval', explode(' ', $format)), 2, 0);

        if ($timingHour === 24 && $timingMinutes === 0) {
            $timingHour = 0;
        }

        if ($timingHour > 23 || $timingMinutes > 59) {
            return self::ERROR_INVALID_TIMING;
        }

        /** @var Carbon $timingCarbonCopy */
        $timingNow        = Carbon::now()->second(0);
        $timingCarbonCopy = $timingNow->copy()->setTime($timingHour, $timingMinutes);
        $timingCarbon     = $timingCarbonCopy;
        $timingDiff       = $timingNow->diffInSeconds($timingCarbon, false);

        if ($timingDiff >= 0 && $timingDiff < 600) {
            return self::ERROR_TOO_CLOSEST;
        }

        $timingToday = $timingDiff > 0;

        return null;
    }

    /**
     * Process the reported timing.
     * @param string|null $input   User input.
     * @param Update      $update  Update instance.
     * @param Process     $process Process instance.
     * @return bool
     */
    public static function processTiming(?string $input, Update $update, Process $process): bool
    {
        /** @var Carbon $timingCarbon */
        $inputParsed = self::parseInput($input, $timingCarbon, $timingToday);

        switch ($inputParsed) {
            case self::ERROR_INVALID_FORMAT:
                $botService = BotService::getInstance();
                $botService->sendCancelableMessage(
                    $update->message->from->id,
                    trans('GridCreation.errorTimingInvalid')
                );

                assert(EventService::getInstance()->register(self::EVENT_INVALID_FORMAT));

                return false;
                break;
            case self::ERROR_INVALID_TIMING:
                $botService = BotService::getInstance();
                $botService->sendCancelableMessage(
                    $update->message->from->id,
                    trans('GridCreation.errorTimingInvalid')
                );

                assert(EventService::getInstance()->register(self::EVENT_INVALID_TIMING));

                return false;
                break;
            case self::ERROR_TOO_CLOSEST:
                $botService = BotService::getInstance();
                $botService->sendCancelableMessage(
                    $update->message->from->id,
                    trans('GridCreation.errorTimingTooShort')
                );

                assert(EventService::getInstance()->register(self::EVENT_INVALID_TOO_CLOSEST));

                return false;
                break;
        }

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

        return true;
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
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        if (!self::processTiming($input, $update, $process)) {
            return self::class;
        }

        return TimingConfirmMoment::class;
    }
}
