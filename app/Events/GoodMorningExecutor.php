<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Services\SettingService;
use Application\Services\Telegram\BotService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class GoodMorningExecutor extends Executor
{
    private const PREVIOUS_TYPE = 'previousType';

    private const TYPES = [
        0 => self::TYPE_EARLY,
        1 => self::TYPE_MORNING,
        2 => self::TYPE_AFTERNOON,
        3 => self::TYPE_EVENING,
    ];

    private const TYPE_AFTERNOON = 'afternoon';
    private const TYPE_EARLY     = 'early';
    private const TYPE_EVENING   = 'evening';
    private const TYPE_MORNING   = 'morning';

    /**
     * @inheritdoc
     */
    public function run(): ?bool
    {
        if (env('APP_ENV') === 'testing') {
            return true;
        }

        $previousType = SettingService::fromReference($this, self::PREVIOUS_TYPE);

        $now     = Carbon::now();
        $nowType = $this->getNotificationType($now);

        if ($previousType->setting_value !== $nowType) {
            $previousType->setting_value = $nowType;
            $previousType->save();

            $botService = BotService::getInstance();
            $botService->createMessage()
                ->forcePublic()
                ->appendMessage(trans('EdgeCommand.good' . Str::ucfirst($nowType)))
                ->publish();
        }

        return true;
    }

    /**
     * Returns the notification type from a Carbon.
     * @param Carbon $carbon Carbon instance.
     * @return string
     */
    private function getNotificationType(Carbon $carbon): string
    {
        return self::TYPES[(int) floor($carbon->hour / 6)];
    }
}
