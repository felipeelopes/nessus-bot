<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Models\Model;
use Application\Models\UserGamertag;
use Application\Services\Live\LiveService;
use Application\Services\SettingService;
use Application\Services\Telegram\BotService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class CheckAccountExecutor extends Executor
{
    private const LAST_CHECK_REFERENCE = 'lastCheckReference';

    /**
     * @inheritdoc
     */
    public function run(?Model $model = null): ?bool
    {
        if (!env('NBOT_OPTION_XBOX_CHECK')) {
            return true;
        }

        $now           = Carbon::now();
        $minDifference = Carbon::now()->subHour(9);

        /** @var UserGamertag $userGamertagQuery */
        /** @var UserGamertag $userGamertag */
        $userGamertagQuery = UserGamertag::query();
        $userGamertagQuery->where(function (Builder $builder) {
            $builder->whereNull('gamertag_id');
            // $builder->orWhereNull('bungie_account');
            // $builder->orWhereNull('bungie_membership');
        });
        $userGamertagQuery->filterLastTouchBefore(self::LAST_CHECK_REFERENCE, $minDifference);
        $userGamertagQuery->inRandomOrder();
        $userGamertag = $userGamertagQuery->first();

        if (!$userGamertag) {
            return true;
        }

        $setting = SettingService::fromReference($userGamertag, self::LAST_CHECK_REFERENCE);
        $setting->touch();

        if (!$userGamertag->gamertag_id) {
            $liveService  = LiveService::getInstance();
            $liveGamertag = $liveService->getGamertag($userGamertag->gamertag_value);

            if ($liveGamertag === null) {
                if ($now->hour >= 9 && $now->hour <= 21) {
                    $botService = BotService::getInstance();
                    $botService->createMessage()
                        ->forcePublic()
                        ->appendMessage(trans('CheckAccount.cantFoundGamertag', [
                            'mention'  => $userGamertag->user->getMention(true),
                            'gamertag' => $userGamertag->gamertag_value,
                        ]))
                        ->publish();
                }

                return true;
            }

            $userGamertag->gamertag_id = $liveGamertag->id;

            if ($userGamertag->gamertag_value !== $liveGamertag->value) {
                $userGamertag->gamertag_value = $liveGamertag->value;
            }

            $userGamertag->save();
        }

        return true;
    }
}
