<?php

declare(strict_types = 1);

namespace Application\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property UserGamertag $gamertag                Owner Gamertag instance.
 * @property int          $gamertag_id             Owner Gamertag id.
 * @property string       $grid_title              Grid title.
 * @property string|null  $grid_observations       Grid observations.
 * @property int          $grip_players            Grid players limit.
 * @property Carbon       $grid_timing             Grid timing to start.
 * @property string       $grid_status             Grid status.
 * @property string       $grid_status_description Grid status description.
 */
class Grid extends Model
{
    public const STATUS_CANCELED  = 'canceled';
    public const STATUS_FINISHED  = 'finished';
    public const STATUS_GATHERING = 'gathering';
    public const STATUS_PLAYING   = 'playing';
    public const STATUS_WAITING   = 'waiting';

    /**
     * Owner Gamertag related.
     * @return HasOne
     */
    public function gamertag(): HasOne
    {
        return $this->hasOne(UserGamertag::class);
    }
}
