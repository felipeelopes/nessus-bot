<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Models\Model;
use Application\Services\SettingService;
use Application\Services\Telegram\BotService;
use Carbon\Carbon;

class TipsExecutor extends Executor
{
    private const PREVIOUS_AT = 'previousAt';

    /**
     * @inheritdoc
     */
    public function run(?Model $model = null): ?bool
    {
        $now = Carbon::now();

        if ($now->hour < 9 || $now->hour > 21) {
            return true;
        }

        $previousAt = SettingService::fromReference($this, self::PREVIOUS_AT);

        if (!$previousAt->setting_value) {
            $previousAt->setting_value = Carbon::now()->subDay(1)->timestamp;
        }

        $diff = $now->diffInMinutes(Carbon::createFromTimestamp($previousAt->setting_value));

        if ($diff >= 45) {
            $previousAt->setting_value = $now->timestamp;
            $previousAt->save();

            $tips      = trans('Tips.all');
            $randomTip = array_random($tips);

            $botService = BotService::getInstance();
            $botService->createMessage()
                ->forcePublic()
                ->appendMessage(trans('Tips.header', [
                    'id'    => array_search($randomTip, $tips, true) + 1,
                    'count' => count($tips),
                    'tip'   => $randomTip,
                ]))
                ->publish();
        }

        return true;
    }
}
