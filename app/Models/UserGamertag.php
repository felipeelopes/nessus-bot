<?php

declare(strict_types = 1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property User|null $user           User referenced.
 * @property int       $user_id        User referenced id.
 * @property int       $gamertag_id    Gamergag id.
 * @property string    $gamertag_value Gamertag value.
 */
class UserGamertag extends Model
{
    /**
     * Return the referenced user.
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
