<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Adapters\Bungie\Activity as ActivityAdapter;
use Application\Models\Activity;
use Application\Models\Model;
use Application\Models\User;
use Application\Services\Bungie\BungieService;
use Application\Services\SettingService;
use Application\Services\UserExperienceService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use RuntimeException;

class CheckActivitiesExecutor extends Executor
{
    private const LAST_CHECKUP = 'lastCheckup';

    /**
     * Process user activites.
     */
    public static function processActivities(User $user, ?bool $avoidCache = null)
    {
        if (!$user->gamertag) {
            return;
        }

        /** @var Activity $lastActivity */
        $lastActivityQuery = Activity::query();
        $lastActivityQuery->where('user_id', $user->id);
        $lastActivityQuery->orderBy('created_at', 'desc');
        $lastActivity = $lastActivityQuery->first([ 'created_at' ]);

        $bungieService = BungieService::getInstance();
        $characters    = $bungieService->getCharacters($user->gamertag->bungie_membership);

        $collectLimit = Carbon::now()->subDays(45);

        $characterRecentActivities = new Collection;

        foreach ($characters as $ck => $character) {
            /** @var Collection|ActivityAdapter[] $characterRecentActivities */
            $characterRecentActivitiesPage = 0;

            do {
                /** @var Collection $characterActivities */
                $characterActivities = $bungieService->getCharacterActivities($user->gamertag->bungie_membership, $character->characterId,
                    $characterRecentActivitiesPage, null, 7, $avoidCache);

                if ($lastActivity) {
                    $characterActivities = $characterActivities->filter(function (ActivityAdapter $activity) use ($collectLimit, $lastActivity) {
                        return $activity->period->gt($lastActivity->created_at) &&
                               $activity->period->gt($collectLimit);
                    });
                }

                if ($characterActivities->isEmpty()) {
                    break;
                }

                $characterActivities = $characterActivities->where('mode', '!=', 2);

                $characterRecentActivities = $characterRecentActivities->merge($characterActivities);
                $characterRecentActivitiesPage++;
            }
            while (true);
        }

        $characterRecentActivities = $characterRecentActivities->reverse();

        foreach ($characterRecentActivities as $characterRecentActivity) {
            $checkActivity = Activity::query();
            $checkActivity->where('user_id', $user->id);
            $checkActivity->where('activity_instance', $characterRecentActivity->instanceId);

            if ($checkActivity->exists()) {
                continue;
            }

            $carnageReport = $bungieService->getMemberCarnageReport($characterRecentActivity, $user->gamertag->bungie_membership);

            $activity                    = new Activity;
            $activity->user_id           = $user->id;
            $activity->activity_instance = $carnageReport->activity->instanceId;
            $activity->activity_mode     = $carnageReport->activity->mode;
            $activity->player_light      = $carnageReport->playerLightLevel;
            $activity->value_completed   = $carnageReport->completed;
            $activity->value_kills       = $carnageReport->kills;
            $activity->value_assists     = $carnageReport->assists;
            $activity->value_deaths      = $carnageReport->deaths;
            $activity->value_precision   = $carnageReport->precisionKills;
            $activity->value_duration    = $carnageReport->timePlayed;
            $activity->created_at        = $carnageReport->activity->period;
            $activity->updated_at        = $carnageReport->activity->period;
            $activity->save();
        }

        UserExperienceService::getInstance()->forgetGlobalRanking();
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
        $usersQuery->whereHas('gamertag', function (Builder $builder) {
            $builder->whereNotNull('bungie_membership');
        });

        $users = $usersQuery->get();

        foreach ($users as $user) {
            try {
                printf("\nProcessing %s...", $user->gamertag->gamertag_value);
                static::processActivities($user);
            }
            catch (RuntimeException $runtimeException) {
                continue;
            }
        }

        return true;
    }
}
