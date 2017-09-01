<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Models\Model;
use Application\Models\UserGamertag;
use Application\Services\Bungie\BungieService;
use Application\Services\Live\LiveService;
use Application\Services\SettingService;
use Application\Services\Telegram\BotService;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

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
        $minDifference = Carbon::now()->subHour(6);

        /** @var UserGamertag $userGamertagQuery */
        /** @var UserGamertag $userGamertag */
        $userGamertagQuery = UserGamertag::query();
        $userGamertagQuery->where(function (Builder $builder) {
            $builder->whereNull('gamertag_id');
            // $builder->orWhereNull('bungie_account');
            // $builder->orWhereNull('bungie_membership');
        });
        $userGamertagQuery->where(function (Builder $builder) use ($minDifference) {
            $builder->whereNotExists(function (QueryBuilder $builder) {
                $builder->select('id');
                $builder->from('settings');
                $builder->where('reference_type', UserGamertag::class);
                $builder->where('reference_id', DB::raw('nbot_user_gamertags.id'));
                $builder->limit(1);
            });
            $builder->orWhereExists(function (QueryBuilder $builder) use ($minDifference) {
                $builder->select('id');
                $builder->from('settings');
                $builder->where('reference_type', UserGamertag::class);
                $builder->where('reference_id', DB::raw('nbot_user_gamertags.id'));
                $builder->where('setting_name', self::LAST_CHECK_REFERENCE);
                $builder->where('updated_at', '<=', $minDifference);
                $builder->limit(1);
            });
        });
        $userGamertagQuery->inRandomOrder();
        $userGamertag = $userGamertagQuery->first();

        if (!$userGamertag) {
            return true;
        }

        $setting = SettingService::fromReference($userGamertag, self::LAST_CHECK_REFERENCE);
        $setting->save();

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
