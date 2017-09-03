<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Models\Model;
use Application\Models\Setting;
use Application\Services\SettingService;
use Application\Services\Telegram\BotService;
use Carbon\Carbon;

class CountdownExecutor extends Executor
{
    private const PREVIOUS_AT = 'previousAt';

    /**
     * Send message.
     */
    private static function sendMessage(Carbon $now, Setting $previousAt, Carbon $launchDate): void
    {
        $previousAt->setting_value = $now->timestamp;
        $previousAt->save();

        $diff    = $now->diff($launchDate);
        $message = [];

        if ($diff->d) {
            $message[] = Carbon::getTranslator()->transChoice('day', $diff->d, [ ':count' => $diff->d ]);
        }

        if ($diff->h) {
            $message[] = Carbon::getTranslator()->transChoice('hour', $diff->h, [ ':count' => $diff->h ]);
        }

        if ($diff->d === 0 && $diff->h === 0 && $diff->m) {
            $message[] = Carbon::getTranslator()->transChoice('minute', $diff->m, [ ':count' => $diff->m ]);
        }

        $botService = BotService::getInstance();
        $botService->createMessage()
            ->forcePublic()
            ->appendMessage(trans('Countdown.countdown', [
                'hours' => implode(' e ', $message),
            ]))
            ->publish();
    }

    /**
     * @inheritdoc
     */
    public function run(?Model $model = null): ?bool
    {
        $now        = Carbon::now();
        $previousAt = SettingService::fromReference($this, self::PREVIOUS_AT);
        $launchDate = new Carbon('2017-09-06 00:00:00');

        if ($now->gt($launchDate)) {
            return true;
        }

        if (!$previousAt->setting_value) {
            $previousAt->setting_value = Carbon::yesterday()->timestamp;
        }

        if ($now->diffInMinutes($launchDate) < 60) {
            self::sendMessage($now, $previousAt, $launchDate);

            return true;
        }

        $diff = $now->diffInMinutes(Carbon::createFromTimestamp($previousAt->setting_value));

        if ($diff >= 60) {
            self::sendMessage($now, $previousAt, $launchDate);
        }

        return true;
    }
}
