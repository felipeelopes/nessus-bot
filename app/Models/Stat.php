<?php

declare(strict_types = 1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * @property User   $user       User reference.
 * @property int    $user_id    User reference ID.
 * @property string $stat_name  Stat name.
 * @property float  $stat_value Stat value.
 */
class Stat extends Model
{
    public const MODE_ABSOLUTE = 'absolute';
    public const MODE_DAILY    = 'daily';

    public const ORDER_ASC  = 'asc';
    public const ORDER_DESC = 'desc';

    public const TYPE_FLOAT      = 'float';
    public const TYPE_INT        = 'int';
    public const TYPE_METERS     = 'meters';
    public const TYPE_PERCENTUAL = 'percentual';
    public const TYPE_TIME       = 'time';

    /**
     * Model casts.
     * @var string[]
     */
    protected $casts = [
        'stat_value' => 'float',
    ];

    /**
     * Return the stats types.
     */
    public static function getStatsTypes(): Collection
    {
        $groupAdventures  = trans('Stats.groupAdventures');
        $groupIluminateds = trans('Stats.groupIluminateds');
        $groupAssists     = trans('Stats.groupAssists');
        $groupHawkEye     = trans('Stats.groupHawkEye');
        $groupTriggers    = trans('Stats.groupTriggers');
        $groupInvencibles = trans('Stats.groupInvencibles');

        return (new Collection([
            [
                'group' => $groupAdventures,
                'name'  => 'secondsPlayed',
                'title' => trans('Stats.titleSecondsPlayed'),
                'type'  => static::TYPE_TIME,
                'mode'  => static::MODE_ABSOLUTE,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'averageLifespan',
                'title' => trans('Stats.titleAverageLifespan'),
                'type'  => static::TYPE_TIME,
                'mode'  => static::MODE_ABSOLUTE,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'longestSingleLife',
                'title' => trans('Stats.titleLongestSingleLife'),
                'type'  => static::TYPE_TIME,
                'mode'  => static::MODE_ABSOLUTE,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'publicEventsCompleted',
                'title' => trans('Stats.titlePublicEventsCompleted'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'heroicPublicEventsCompleted',
                'title' => trans('Stats.titleHeroicPublicEventsCompleted'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'adventuresCompleted',
                'title' => trans('Stats.titleAdventuresCompleted'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'activitiesCleared',
                'title' => trans('Stats.titleActivitiesCleared'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupAdventures,
                'name'  => 'bestSingleGameKills',
                'title' => trans('Stats.titleBestSingleGameKills'),
                'type'  => static::TYPE_INT,
                'mode'  => static::MODE_ABSOLUTE,
                'order' => static::ORDER_ASC,
            ],

            [
                'group' => $groupIluminateds,
                'name'  => 'weaponKillsSuper',
                'title' => trans('Stats.titleWeaponKillsSuper'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupIluminateds,
                'name'  => 'weaponKillsGrenade',
                'title' => trans('Stats.titleWeaponKillsGrenade'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupIluminateds,
                'name'  => 'weaponKillsMelee',
                'title' => trans('Stats.titleWeaponKillsMelee'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupIluminateds,
                'name'  => 'orbsDropped',
                'title' => trans('Stats.titleOrbsDropped'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupIluminateds,
                'name'  => 'orbsGathered',
                'title' => trans('Stats.titleOrbsGathered'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],

            [
                'group' => $groupAssists,
                'name'  => 'assists',
                'title' => trans('Stats.titleAssists'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupAssists,
                'name'  => 'resurrectionsPerformed',
                'title' => trans('Stats.titleResurrectionsPerformed'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupAssists,
                'name'  => 'killsDeathsAssists',
                'title' => trans('Stats.titleKillsDeathsAssists'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_ABSOLUTE,
                'order' => static::ORDER_ASC,
            ],

            [
                'group' => $groupHawkEye,
                'name'  => 'longestKillDistance',
                'title' => trans('Stats.titleLongestKillDistance'),
                'type'  => static::TYPE_METERS,
                'mode'  => static::MODE_ABSOLUTE,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupHawkEye,
                'name'  => 'averageKillDistance',
                'title' => trans('Stats.titleAverageKillDistance'),
                'type'  => static::TYPE_METERS,
                'mode'  => static::MODE_ABSOLUTE,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupHawkEye,
                'name'  => 'totalKillDistance',
                'title' => trans('Stats.titleTotalKillDistance'),
                'type'  => static::TYPE_METERS,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupHawkEye,
                'name'  => 'precisionKills',
                'title' => trans('Stats.titlePrecisionKills'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupHawkEye,
                'name'  => 'mostPrecisionKills',
                'title' => trans('Stats.titleMostPrecisionKills'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],

            [
                'group' => $groupTriggers,
                'name'  => 'kills',
                'title' => trans('Stats.titleKills'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsAutoRifle',
                'title' => trans('Stats.titleWeaponKillsAutoRifle'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsFusionRifle',
                'title' => trans('Stats.titleWeaponKillsFusionRifle'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsHandCannon',
                'title' => trans('Stats.titleWeaponKillsHandCannon'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsMachinegun',
                'title' => trans('Stats.titleWeaponKillsMachinegun'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsPulseRifle',
                'title' => trans('Stats.titleWeaponKillsPulseRifle'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsRocketLauncher',
                'title' => trans('Stats.titleWeaponKillsRocketLauncher'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsScoutRifle',
                'title' => trans('Stats.titleWeaponKillsScoutRifle'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsShotgun',
                'title' => trans('Stats.titleWeaponKillsShotgun'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsSniper',
                'title' => trans('Stats.titleWeaponKillsSniper'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsSubmachinegun',
                'title' => trans('Stats.titleWeaponKillsSubmachinegun'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsSideArm',
                'title' => trans('Stats.titleWeaponKillsSideArm'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsSword',
                'title' => trans('Stats.titleWeaponKillsSword'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'weaponKillsRelic',
                'title' => trans('Stats.titleWeaponKillsRelic'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'longestKillSpree',
                'title' => trans('Stats.titleLongestKillSpree'),
                'type'  => static::TYPE_INT,
                'mode'  => static::MODE_ABSOLUTE,
                'order' => static::ORDER_ASC,
            ],
            [
                'group' => $groupTriggers,
                'name'  => 'killsDeathsRatio',
                'title' => trans('Stats.titleKillsDeathsRatio'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_ABSOLUTE,
                'order' => static::ORDER_ASC,
            ],

            [
                'group' => $groupInvencibles,
                'name'  => 'deaths',
                'title' => trans('Stats.titleDeaths'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_DESC,
            ],
            [
                'group' => $groupInvencibles,
                'name'  => 'suicides',
                'title' => trans('Stats.titleSuicides'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_DESC,
            ],
            [
                'group' => $groupInvencibles,
                'name'  => 'resurrectionsReceived',
                'title' => trans('Stats.titleResurrectionsReceived'),
                'type'  => static::TYPE_FLOAT,
                'mode'  => static::MODE_DAILY,
                'order' => static::ORDER_DESC,
            ],
        ]))->keyBy('name');
    }

    /**
     * Get formatted value.
     */
    public function getFormattedValue()
    {
        $statType  = static::getStatsTypes()->get($this->stat_name);
        $statValue = $this->stat_value;

        if ($statType) {
            switch ($statType['type']) {
                case static::TYPE_FLOAT:
                    $statValue = sprintf('%.1f', $statValue);
                    break;
                case static::TYPE_TIME:
                    $statValue = $statValue >= 3600
                        ? sprintf('%.1f ' . trans('Stats.typeHours'), $statValue / 3600)
                        : sprintf('%.1f ' . trans('Stats.typeMinutes'), $statValue / 60);
                    break;
                case static::TYPE_METERS:
                    $statValue = sprintf('%.1f %s', $statValue, trans('Stats.typeMeters'));
                    break;
                case static::TYPE_PERCENTUAL:
                    $statValue = sprintf('%.2f%%', $statValue);
                    break;
            }

            if ($statType['mode'] === static::MODE_DAILY) {
                $statValue = trans('Stats.modeDaily', [ 'value' => $statValue ]);
            }
        }

        return $statValue;
    }

    /**
     * Get percent value based on the best one.
     * @return float
     */
    public function getPercentFrom(float $percent, bool $reverse): float
    {
        return $reverse
            ? (min(100, $this->stat_value) - 100) / (min(100, $percent) - 100)
            : $this->stat_value / $percent;
    }

    /**
     * Return the referenced user.
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
