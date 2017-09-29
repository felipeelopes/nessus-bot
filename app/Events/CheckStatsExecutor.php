<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Models\Model;
use Application\Models\Stat;
use Application\Models\User;
use Application\Services\Bungie\BungieService;
use Application\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CheckStatsExecutor extends Executor
{
    private const LAST_CHECKUP = 'lastCheckup';

    /**
     * Request an updated stats from a specific user.
     * @param User $user
     * @throws \Exception
     */
    public static function requestStats($user): void
    {
        $bungieService = BungieService::getInstance();
        $userGamertag  = $user->gamertag;

        if (!$userGamertag) {
            return;
        }

        $membership = $userGamertag->bungie_membership;

        if (!$membership) {
            return;
        }

        $userStats = $bungieService->userStatsSimplified($membership);

        $statsQuery = Stat::query();
        $statsQuery->where('user_id', $user->id);
        $stats = $statsQuery->get()
            ->keyBy('stat_name');

        if ($userStats && $userStats->get('highestCharacterLevel') >= 20) {
            foreach ($userStats as $userStatKey => $userStatValue) {
                static::registerStat($user, $stats, $userStatKey, $userStatValue);
            }
        }
    }

    /**
     * Register a stat.
     */
    private static function registerStat(User $user, Collection $stats, string $statName, $statValue): void
    {
        $statRegister = $stats->get($statName);

        if ($statValue !== null) {
            if ($statRegister === null) {
                $statRegister            = new Stat;
                $statRegister->user_id   = $user->id;
                $statRegister->stat_name = $statName;
            }

            $statRegister->stat_value = $statValue;
            $statRegister->touch();
        }
    }

    /**
     * @inheritdoc
     */
    public function run(?Model $model = null): ?bool
    {
        if (Carbon::now()->hour < 20) {
            return true;
        }

        $lastCheckup = SettingService::fromReference($this, static::LAST_CHECKUP);

        if ($lastCheckup->exists &&
            $lastCheckup->updated_at->diffInHours(Carbon::now()) < 23) {
            return true;
        }

        $lastCheckup->touch();

        /** @var User|Builder $usersQuery */
        $usersQuery = User::query();
        $usersQuery->with('gamertag');
        $usersQuery->where('updated_at', '>=', Carbon::now()->subDays(3));
        $usersQuery->whereHas('gamertag', function (Builder $builder) {
            $builder->whereNotNull('bungie_membership');
        });

        $users = $usersQuery->get();

        foreach ($users as $user) {
            self::requestStats($user);
        }

        return true;
    }
}
