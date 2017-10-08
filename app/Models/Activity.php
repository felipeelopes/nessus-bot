<?php

declare(strict_types = 1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property User     $user               User reference.
 * @property int      $user_id            User reference ID.
 * @property int      $activity_instance  Activity instance.
 * @property int      $activity_mode      Activity mode.
 * @property boolean  $activity_validated Activity was validated by the owner user?
 * @property int|null $player_light       Player light.
 * @property boolean  $value_completed    Activity was complete.
 * @property int      $value_kills        Number of kills from player.
 * @property int      $value_assists      Number of assists from player.
 * @property int      $value_deaths       Number of deaths from player.
 * @property int      $value_precision    Number of precision kills from player.
 * @property int      $value_duration     Number of seconds on activity from player.
 */
class Activity extends Model
{
    /**
     * Model casts.
     * @var string[]
     */
    protected $casts = [
        'activity_validated' => 'boolean',
        'value_completed'    => 'boolean',
    ];

    /**
     * Return the referenced user.
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
