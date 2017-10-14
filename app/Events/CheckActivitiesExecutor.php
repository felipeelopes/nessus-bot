<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Adapters\Bungie\Activity as ActivityAdapter;
use Application\Adapters\Ranking\PlayerRanking;
use Application\Models\Activity;
use Application\Models\Model;
use Application\Models\User;
use Application\Models\UserGamertag;
use Application\Services\Bungie\BungieService;
use Application\Services\Requester\RequesterService;
use Application\Services\SettingService;
use Application\Services\Telegram\BotService;
use Application\Services\UserExperienceService;
use Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use RuntimeException;

class CheckActivitiesExecutor extends Executor
{
    private const LAST_CHECKUP = 'lastCheckup';

    /**
     * Process user activites.
     * @throws \Exception
     */
    public static function processActivities(User $user, ?bool $avoidCache = null)
    {
        if (!$user->gamertag || !$user->gamertag->bungie_membership) {
            return;
        }

        /** @var Activity $lastActivity */
        $lastActivityQuery = Activity::query();
        $lastActivityQuery->where('user_id', $user->id);
        $lastActivityQuery->where('activity_validated', true);
        $lastActivityQuery->where('created_at', '<=', Carbon::now()->subDay());
        $lastActivityQuery->orderBy('created_at', 'desc');
        $lastActivity = $lastActivityQuery->first([ 'created_at' ]);

        $bungieService = BungieService::getInstance();
        $characters    = $bungieService->getCharacters($user->gamertag->bungie_membership);

        $collectLimit = (new Carbon)->startOfYear();

        $characterRecentActivities = new Collection;
        $charactersCount           = 0;

        foreach ($characters as $characterKey => $character) {
            /** @var Collection|ActivityAdapter[] $characterRecentActivities */
            $characterRecentActivitiesPage = 0;

            printf("\n\tCharacter #%u:", $characterKey);

            $characterCacheKey   = __CLASS__ . '@' . __FUNCTION__ . '@characterId' . $character->characterId . ':lastPlayed';
            $characterLastPlayed = Cache::get($characterCacheKey);

            if ($character->dateLastPlayedString === $characterLastPlayed) {
                continue;
            }

            Cache::put($characterCacheKey, $character->dateLastPlayedString, RequesterService::CACHE_MONTH);
            $charactersCount++;

            do {
                printf(' #%u', $characterRecentActivitiesPage);

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

        $characterRecentActivities = $characterRecentActivities->reverse()->values();

        printf("\n\tCharacters...: %u\n\tActivities...: %u",
            $charactersCount,
            $characterRecentActivities->count());

        /** @var UserGamertag|mixed $usersQuery */
        $usersQuery = UserGamertag::query();
        $usersQuery->withTrashed();
        $users = $usersQuery->get()->keyBy('bungie_membership');

        if ($characterRecentActivities->count()) {
            printf("\n\tCarnages.....:");
        }

        foreach ($characterRecentActivities as $characterRecentActivityKey => $characterRecentActivity) {
            $carnageReports = $bungieService->getMemberCarnageReport($characterRecentActivity);

            /** @var Collection|Activity[] $checkActivities */
            $checkActivitiesQuery = Activity::query();
            $checkActivitiesQuery->where('activity_instance', $characterRecentActivity->instanceId);
            $checkActivities = $checkActivitiesQuery->get();

            printf(' #%u', $characterRecentActivityKey);

            foreach ($carnageReports as $carnageReport) {
                /** @var User $carnageUser */
                $carnageUser = $users->get($carnageReport->membershipId);

                if (!$carnageUser) {
                    continue;
                }

                if ($carnageUser->deleted_at &&
                    $carnageReport->activity->period->gt(new Carbon($carnageUser->deleted_at))) {
                    continue;
                }

                /** @var Activity|null $activity */
                $activity = $checkActivities
                                ->where('user_id', $carnageUser->id)
                                ->first()
                            ?? new Activity;

                $userValidated = $user->gamertag->bungie_membership === $carnageReport->membershipId;

                $activity->player_light    = $carnageReport->playerLightLevel;
                $activity->value_completed = $carnageReport->completed;
                $activity->value_kills     = $carnageReport->kills;
                $activity->value_assists   = $carnageReport->assists;
                $activity->value_deaths    = $carnageReport->deaths;
                $activity->value_precision = $carnageReport->precisionKills;
                $activity->value_duration  = $carnageReport->timePlayed;

                if ($userValidated) {
                    if ($activity->exists && !$activity->activity_validated) {
                        $activity->activity_validated = true;
                        $activity->save();
                    }

                    continue;
                }

                $activity->user_id           = $carnageUser->id;
                $activity->activity_instance = $carnageReport->activity->instanceId;
                $activity->activity_mode     = $carnageReport->activity->mode;
                $activity->created_at        = $carnageReport->activity->period;
                $activity->updated_at        = $carnageReport->activity->period;
                $activity->save();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function run(?Model $model = null): ?bool
    {
        $lastCheckup = SettingService::fromReference($this, static::LAST_CHECKUP);

        if ($lastCheckup->exists) {
            $now = Carbon::now();

            if ($now->hour < 20 ||
                $lastCheckup->updated_at->diffInHours($now) < 23) {
                return true;
            }
        }

        $botService     = BotService::getInstance();
        $playerRankings = PlayerRanking::fromQuery(UserExperienceService::queryGlobalRanking($lastCheckup->updated_at));

        $lastCheckup->touch();

        /** @var User|Builder $usersQuery */
        $usersQuery = User::query();
        $usersQuery->with('gamertag');
        $usersQuery->whereHas('gamertag', function (Builder $builder) {
            $builder->whereNotNull('bungie_membership');
        });

        $users = $usersQuery->get()
            ->keyBy('id');

        foreach ($users as $user) {
            try {
                printf("\n\nProcessing %s... ", $user->gamertag->gamertag_value);
                static::processActivities($user);
            }
            catch (RuntimeException $runtimeException) {
                continue;
            }
        }

        $userExperienceService = UserExperienceService::getInstance();
        $userExperienceService->forgetGlobalRanking();

        $updatedPlayerRankings = $userExperienceService->getGlobalRanking();

        /** @var PlayerRanking $updatedPlayerRanking */
        foreach ($updatedPlayerRankings as $updatedPlayerRankingKey => $updatedPlayerRanking) {
            /** @var PlayerRanking $playerRanking */
            $playerRanking = $playerRankings->get($updatedPlayerRankingKey)
                             ?? new PlayerRanking;

            if (!$playerRanking ||
                $updatedPlayerRanking->player_experience - $playerRanking->player_experience >= 1) {
                /** @var User|null $player */
                $player = $users->get($updatedPlayerRankingKey);

                if (!$player) {
                    continue;
                }

                $message = trans('Ranking.dailyReport', [
                    'xpAdded' => number_format($updatedPlayerRanking->player_experience - $playerRanking->player_experience, 0, '', '.'),
                    'xpTotal' => number_format($updatedPlayerRanking->player_experience, 0, '', '.'),
                ]);

                $playerLevel = $playerRanking->getLevel();

                if ($playerLevel->getNextExperience() !== null) {
                    $updatedPlayerLevel = $updatedPlayerRanking->getLevel();

                    $message .= $updatedPlayerLevel->level > $playerLevel->level
                        ? trans('Ranking.dailyLevelAdvanced', [
                            'level' => $updatedPlayerLevel->getIconTitle(true),
                        ])
                        : trans('Ranking.dailyLevelSame', [
                            'level'      => $updatedPlayerLevel->getIconTitle(true),
                            'xpRequired' => number_format($updatedPlayerLevel->getNextExperience(), 0, '', '.'),
                        ]);
                }

                printf("Notifying %s...\n", $player->gamertag->gamertag_value);

                $botService->createMessage()
                    ->appendMessage($message)
                    ->setReceiver($player->user_number)
                    ->publish();
            }
        }

        return true;
    }
}
