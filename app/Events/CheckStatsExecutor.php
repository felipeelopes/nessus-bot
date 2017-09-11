<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Models\Model;
use Application\Models\Setting;
use Application\Models\User;
use Application\Services\Bungie\BungieService;
use Application\Services\SettingService;
use Application\Strategies\UserStatsStrategy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CheckStatsExecutor extends Executor
{
    /**
     * Request an updated stats from a specific user.
     * @param User $user
     */
    public static function requestStats($user): Collection
    {
        $userStatsStrategyReference = new UserStatsStrategy;

        /** @var Setting $settingsQuery */
        $settingsQuery = Setting::query();
        $settingsQuery->filterMorphReference($userStatsStrategyReference);
        $settings = $settingsQuery->get();

        $bungieService = BungieService::getInstance();
        $userGamertag  = $user->gamertag;

        if (!$userGamertag) {
            return new Collection;
        }

        $membership = $userGamertag->bungie_membership;
        $userStats  = $bungieService->userStatsSimplified($membership);

        if ($userStats) {
            foreach ($userStats as $statName => $statValue) {
                self::registerStat($settings, $statName, $statValue, $user);
            }

            if ($userStats->has('activitiesEntered') &&
                $userStats->has('activitiesCleared') &&
                $userStats->get('activitiesCleared') >= 10) {
                self::registerStat($settings, 'activitiesReason', $userStats->get('activitiesCleared') / $userStats->get('activitiesEntered') * 100, $user);
            }
        }

        $settingUserUpdatedReference = SettingService::fromReference($user, UserStatsStrategy::UPDATED_AT);
        $settingUserUpdatedReference->touch();

        $settingStatsUpdatedReference = SettingService::fromReference($userStatsStrategyReference, UserStatsStrategy::UPDATED_AT);
        $settingStatsUpdatedReference->touch();

        return $userStats;
    }

    /**
     * Register a stat.
     * @param Collection|Setting[] $settings
     */
    private static function registerStat(Collection $settings, string $statName, $statValue, User $referenceUser): void
    {
        $statNameReference = $statName . UserStatsStrategy::TYPE_VALUE;

        /** @var Setting|null $settingValueReference */
        $settingValueReference = $settings->first(function (Setting $setting) use ($statNameReference) {
            return $setting->setting_name === $statNameReference;
        });

        if (!$settingValueReference) {
            $settingValueReference                 = new Setting;
            $settingValueReference->reference_type = UserStatsStrategy::class;
            $settingValueReference->setting_name   = $statNameReference;
        }

        $statUserIdReference = $statName . UserStatsStrategy::TYPE_USER_ID;

        /** @var Setting|null $settingUserIdReference */
        $settingUserIdReference = $settings->first(function (Setting $setting) use ($statUserIdReference) {
            return $setting->setting_name === $statUserIdReference;
        });

        if (!$settingUserIdReference) {
            $settingUserIdReference                 = new Setting;
            $settingUserIdReference->reference_type = UserStatsStrategy::class;
            $settingUserIdReference->setting_name   = $statUserIdReference;
        }

        if (!$settingValueReference->exists ||
            $settingValueReference->setting_value < $statValue) {
            $settingValueReference->setting_value = $statValue;
            $settingValueReference->save();

            $settingUserIdReference->setting_value = $referenceUser->id;
            $settingUserIdReference->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function run(?Model $model = null): ?bool
    {
        $userStatsStrategyReference = new UserStatsStrategy;

        /** @var Setting $settingsQuery */
        $settingsQuery = Setting::query();
        $settingsQuery->filterMorphReference($userStatsStrategyReference);
        $settingsQuery->where('setting_name', UserStatsStrategy::REQUESTED);
        $requesterIds = $settingsQuery->pluck('id');

        /** @var User|Builder $usersQuery */
        $usersQuery = User::query();
        $usersQuery->with('gamertag');
        $usersQuery->where('updated_at', '>=', Carbon::now()->subDays(3));
        $usersQuery->filterLastTouchBefore(UserStatsStrategy::UPDATED_AT, Carbon::now()->subHours(12));
        $usersQuery->whereHas('gamertag', function (Builder $builder) {
            $builder->whereNotNull('bungie_membership');
        });

        if ($requesterIds->count()) {
            $usersQuery->whereIn('id', $requesterIds);
        }

        $usersQuery->inRandomOrder();
        $user = $usersQuery->first();

        if (!$user) {
            return true;
        }

        self::requestStats($user);

        return true;
    }
}
