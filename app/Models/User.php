<?php

declare(strict_types = 1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property-read Collection|UserGamertag[] $gamertags          User gametags.
 * @property int                            $user_number        User number identification from Telegram.
 * @property string                         $user_username      User username.
 * @property string                         $user_firstname     User first name.
 * @property string                         $user_lastname      User last name.
 * @property string                         $user_language      User language tag (IETF).
 * @method Builder whereUserNumber(int $userNumber)
 */
class User extends Model
{
    /**
     * Returns all gamertags related to user.
     * @return HasMany
     */
    public function gamertags(): HasMany
    {
        return $this->hasMany(UserGamertag::class);
    }

    /**
     * Returns the main Gamertag from User.
     * @return UserGamertag|null
     */
    public function getGamertag(): ?UserGamertag
    {
        return $this->gamertags->first();
    }
}
