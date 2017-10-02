<?php

declare(strict_types = 1);

namespace Application\Adapters\Bungie;

use Application\Adapters\BaseFluent;

/**
 * @property Activity $activity         Activity instance.
 * @property int      $membershipId     Membership id.
 * @property int|null $playerLightLevel Player light level.
 * @property bool     $completed        Event was complete.
 * @property int      $kills            Player kills.
 * @property int      $deaths           Player deaths.
 * @property int      $assists          Player assists.
 * @property int      $timePlayed       Player duration on event.
 * @property int      $precisionKills   Player precision kills.
 */
class CarnageReportEntry extends BaseFluent
{
    /**
     * BaseFluent constructor.
     * @param array|null $attributes
     */
    public function __construct($attributes = null, Activity $activity)
    {
        parent::__construct();

        $this->activity         = $activity;
        $this->membershipId     = (int) array_get($attributes, 'player.destinyUserInfo.membershipId');
        $this->playerLightLevel = ((int) array_get($attributes, 'player.lightLevel')) ?: null;
        $this->completed        = (bool) array_get($attributes, 'values.completed.basic.value');
        $this->kills            = (int) array_get($attributes, 'values.kills.basic.value');
        $this->deaths           = (int) array_get($attributes, 'values.deaths.basic.value');
        $this->assists          = (int) array_get($attributes, 'values.assists.basic.value');
        $this->timePlayed       = (int) array_get($attributes, 'values.timePlayedSeconds.basic.value');
        $this->precisionKills   = (int) array_get($attributes, 'extended.values.precisionKills.basic.value');
    }

    /**
     * Merge this entry values with another one.
     * @return CarnageReportEntry
     */
    public function mergeWith(CarnageReportEntry $anotherEntry): CarnageReportEntry
    {
        if ($anotherEntry->playerLightLevel !== null) {
            if ($this->playerLightLevel === null ||
                $this->playerLightLevel < $anotherEntry->playerLightLevel) {
                $this->playerLightLevel = $anotherEntry->playerLightLevel;
            }
        }

        $this->completed      = $this->completed || $anotherEntry->completed;
        $this->kills          += $anotherEntry->kills;
        $this->deaths         += $anotherEntry->deaths;
        $this->assists        += $anotherEntry->assists;
        $this->timePlayed     += $anotherEntry->timePlayed;
        $this->precisionKills += $anotherEntry->precisionKills;

        return $this;
    }
}
