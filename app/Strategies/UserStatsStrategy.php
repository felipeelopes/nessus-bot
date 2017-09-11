<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Events\CheckStatsExecutor;
use Application\Models\Setting;
use Application\Models\User;
use Application\Services\CommandService;
use Application\Services\SettingService;
use Application\Services\Telegram\BotService;
use Application\Strategies\Contracts\UserStrategyContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class UserStatsStrategy implements UserStrategyContract
{
    public const  REQUESTED    = 'requested';
    public const  TYPE_USER_ID = '@userId';
    public const  TYPE_VALUE   = '@value';
    public const  UPDATED_AT   = 'statsUpdatedAt';

    /**
     * Format stat value.
     */
    private static function formatValue(string $statsType, $statsValue)
    {
        if ($statsType) {
            switch ($statsType) {
                case 'float':
                    $statsValue = sprintf('%.1f', $statsValue);
                    break;
                case 'time':
                    $statsValue = $statsValue >= 3600
                        ? sprintf('%.1f ' . trans('Stats.typeHours'), $statsValue / 3600)
                        : sprintf('%.1f ' . trans('Stats.typeMinutes'), $statsValue / 60);
                    break;
                case 'm':
                    $statsValue = sprintf('%.1f %s', $statsValue, trans('Stats.typeMeters'));
                    break;
                case 'percentual':
                    $statsValue = sprintf('%.2f%%', $statsValue);
                    break;
            }
        }

        return $statsValue;
    }

    /**
     * Return the stats types.
     */
    private static function getStatsTypes(): array
    {
        $groupAdventures  = trans('Stats.groupAdventures');
        $groupIluminateds = trans('Stats.groupIluminateds');
        $groupAssists     = trans('Stats.groupAssists');
        $groupHawkEye     = trans('Stats.groupHawkEye');
        $groupTriggers    = trans('Stats.groupTriggers');
        $groupBaggers     = trans('Stats.groupBaggers');

        return [
            [
                'group' => $groupAdventures,
                'name'  => 'totalActivityDurationSeconds',
                'title' => trans('Stats.titleTotalActivityDurationSeconds'),
                'type'  => 'time',
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'secondsPlayed',
                'title' => trans('Stats.titleSecondsPlayed'),
                'type'  => 'time',
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'averageLifespan',
                'title' => trans('Stats.titleAverageLifespan'),
                'type'  => 'time',
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'longestSingleLife',
                'title' => trans('Stats.titleLongestSingleLife'),
                'type'  => 'time',
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'publicEventsCompleted',
                'title' => trans('Stats.titlePublicEventsCompleted'),
                'type'  => 'int',
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'activitiesEntered',
                'title' => trans('Stats.titleActivitiesEntered'),
                'type'  => 'int',
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'activitiesCleared',
                'title' => trans('Stats.titleActivitiesCleared'),
                'type'  => 'int',
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'activitiesReason',
                'title' => trans('Stats.titleActivitiesReason'),
                'type'  => 'percentual',
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'bestSingleGameKills',
                'title' => trans('Stats.titleBestSingleGameKills'),
                'type'  => 'int',
            ],
            [
                'group' => $groupIluminateds,
                'name'  => 'weaponKillsSuper',
                'title' => trans('Stats.titleWeaponKillsSuper'),
                'type'  => 'int',
            ],
            [
                'group' => $groupIluminateds,
                'name'  => 'weaponKillsGrenade',
                'title' => trans('Stats.titleWeaponKillsGrenade'),
                'type'  => 'int',
            ],
            [
                'group' => $groupIluminateds,
                'name'  => 'weaponKillsMelee',
                'title' => trans('Stats.titleWeaponKillsMelee'),
                'type'  => 'int',
            ],
            [
                'group' => $groupIluminateds,
                'name'  => 'orbsDropped',
                'title' => trans('Stats.titleOrbsDropped'),
                'type'  => 'int',
            ],
            [
                'group' => $groupIluminateds,
                'name'  => 'orbsGathered',
                'title' => trans('Stats.titleOrbsGathered'),
                'type'  => 'int',
            ],
            [
                'group' => $groupAssists,
                'name'  => 'assists',
                'title' => trans('Stats.titleAssists'),
                'type'  => 'int',
            ],
            [
                'group' => $groupAssists,
                'name'  => 'resurrectionsPerformed',
                'title' => trans('Stats.titleResurrectionsPerformed'),
                'type'  => 'int',
            ],
            [
                'group' => $groupAssists,
                'name'  => 'killsDeathsAssists',
                'title' => trans('Stats.titleKillsDeathsAssists'),
                'type'  => 'float',
            ],
            [
                'group' => $groupHawkEye,
                'name'  => 'longestKillDistance',
                'title' => trans('Stats.titleLongestKillDistance'),
                'type'  => 'm',
            ],
            [
                'group' => $groupHawkEye,
                'name'  => 'averageKillDistance',
                'title' => trans('Stats.titleAverageKillDistance'),
                'type'  => 'm',
            ],
            [
                'group' => $groupHawkEye,
                'name'  => 'totalKillDistance',
                'title' => trans('Stats.titleTotalKillDistance'),
                'type'  => 'm',
            ],
            [
                'group' => $groupHawkEye,
                'name'  => 'precisionKills',
                'title' => trans('Stats.titlePrecisionKills'),
                'type'  => 'int',
            ],
            [
                'group' => $groupHawkEye,
                'name'  => 'mostPrecisionKills',
                'title' => trans('Stats.titleMostPrecisionKills'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'kills',
                'title' => trans('Stats.titleKills'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsAutoRifle',
                'title' => trans('Stats.titleWeaponKillsAutoRifle'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsFusionRifle',
                'title' => trans('Stats.titleWeaponKillsFusionRifle'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsHandCannon',
                'title' => trans('Stats.titleWeaponKillsHandCannon'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsMachinegun',
                'title' => trans('Stats.titleWeaponKillsMachinegun'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsPulseRifle',
                'title' => trans('Stats.titleWeaponKillsPulseRifle'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsRocketLauncher',
                'title' => trans('Stats.titleWeaponKillsRocketLauncher'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsScoutRifle',
                'title' => trans('Stats.titleWeaponKillsScoutRifle'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsShotgun',
                'title' => trans('Stats.titleWeaponKillsShotgun'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsSniper',
                'title' => trans('Stats.titleWeaponKillsSniper'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsSubmachinegun',
                'title' => trans('Stats.titleWeaponKillsSubmachinegun'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsSideArm',
                'title' => trans('Stats.titleWeaponKillsSideArm'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsSword',
                'title' => trans('Stats.titleWeaponKillsSword'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsRelic',
                'title' => trans('Stats.titleWeaponKillsRelic'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'longestKillSpree',
                'title' => trans('Stats.titleLongestKillSpree'),
                'type'  => 'int',
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'killsDeathsRatio',
                'title' => trans('Stats.titleKillsDeathsRatio'),
                'type'  => 'float',
            ],
            [
                'group' => $groupBaggers,
                'name'  => 'deaths',
                'title' => trans('Stats.titleDeaths'),
                'type'  => 'int',
            ],
            [
                'group' => $groupBaggers,
                'name'  => 'suicides',
                'title' => trans('Stats.titleSuicides'),
                'type'  => 'int',
            ],
            [
                'group' => $groupBaggers,
                'name'  => 'resurrectionsReceived',
                'title' => trans('Stats.titleResurrectionsReceived'),
                'type'  => 'int',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(?User $user, Update $update): ?bool
    {
        if ($user === null) {
            return null;
        }

        if ($update->message->isCommand(CommandService::COMMAND_STATS)) {
            BotService::getInstance()
                ->createMessage($update->message)
                ->appendMessage($this->generateStats())
                ->unduplicate(self::class . '@Command:' . CommandService::COMMAND_STATS)
                ->publish();

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_SELF_STATS)) {
            $identifier = self::class . '@Command:' . CommandService::COMMAND_SELF_STATS;

            BotService::getInstance()
                ->createMessage($update->message)
                ->appendMessage(trans('Stats.selfStatsRequest'))
                ->unduplicate($identifier)
                ->publish();

            $userStats = CheckStatsExecutor::requestStats($user);

            $botMessageService = BotService::getInstance()
                ->createMessage($update->message)
                ->appendMessage($this->generateStats($update->message->from->getUserRegister(), $userStats))
                ->unduplicate($identifier);

            $messageEntityBotCommand = $update->message->getCommand();
            $isPublic                = $messageEntityBotCommand &&
                                       $messageEntityBotCommand->getTextArgument() === 'public';

            if (!$isPublic) {
                $botMessageService->forcePrivate();
            }

            $botMessageService->publish();

            return true;
        }

        return null;
    }

    /**
     * Generate game stats.
     */
    private function generateStats(?User $user = null, ?Collection $userStats = null)
    {
        $contents      = '';
        $previousGroup = null;

        /** @var Setting $settingsQuery */
        $settingsQuery = Setting::query();
        $settingsQuery->filterMorphReference($this);
        $settings = $settingsQuery->get();

        $userIds = $settings->filter(function (Setting $setting) {
            return ends_with($setting->setting_name, self::TYPE_USER_ID);
        })->pluck('setting_value')->unique();

        /** @var User|Builder $usersQuery */
        $usersQuery = User::query();
        $usersQuery->with('gamertag');
        $usersQuery->whereIn('id', $userIds);
        $users = $usersQuery->get()->pluck('gamertag.gamertag_value', 'id');

        foreach (self::getStatsTypes() as $statsType) {
            if ($userStats !== null &&
                !$userStats->has($statsType['name'])) {
                continue;
            }

            if ($statsType['group'] !== $previousGroup) {
                $previousGroup = $statsType['group'];
                $contents      .= trans('Stats.statsGroup', [
                    'title' => $statsType['group'],
                ]);
            }

            /** @var Setting|null $settingValueReference */
            $settingValueReference = $settings->first(function (Setting $setting) use ($statsType) {
                return $setting->setting_name === $statsType['name'] . self::TYPE_VALUE;
            });

            /** @var Setting|null $settingUserIdReference */
            $settingUserIdReference = $settings->first(function (Setting $setting) use ($statsType) {
                return $setting->setting_name === $statsType['name'] . self::TYPE_USER_ID;
            });

            $statsValue    = $settingValueReference
                ? $settingValueReference->setting_value
                : null;
            $statsGamertag = $settingUserIdReference
                ? $users->get($settingUserIdReference->setting_value)
                : null;

            if ($user !== null) {
                $userStatValue  = $userStats->get($statsType['name']);
                $userStatTrophy = $userStatValue >= $statsValue
                    ? trans('Stats.statsTrophy')
                    : null;

                $contents .= trans('Stats.statsItemSelf', [
                    'title'   => $statsType['title'],
                    'value'   => self::formatValue($statsType['type'], $userStatValue) ?: '-',
                    'percent' => sprintf('%.2f%%', 100 / $statsValue * $userStatValue),
                    'trophy'  => $userStatTrophy,
                ]);

                continue;
            }

            $contents .= trans('Stats.statsItem', [
                'title'    => $statsType['title'],
                'value'    => self::formatValue($statsType['type'], $statsValue) ?: '-',
                'gamertag' => $statsGamertag ?: '-',
            ]);
        }

        /** @var Setting $updatedAt */
        $updatedAt = SettingService::fromReference($this, self::UPDATED_AT);
        $updatedAt = $updatedAt->exists
            ? $updatedAt->updated_at->format('d/m/Y \à\s H:i:s')
            : null;

        if ($user !== null) {
            return trans('Stats.statsHeaderSelf', [
                'gamertag' => $user->gamertag->gamertag_value,
                'contents' => $contents,
            ]);
        }

        return trans('Stats.statsHeader', [
            'contents' => $contents,
            'datetime' => $updatedAt ?: '-',
        ]);
    }
}
