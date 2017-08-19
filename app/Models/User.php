<?php

declare(strict_types = 1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read UserGamertag|null $gamertag        User gametag.
 * @property int                    $user_number     User number identification from Telegram.
 * @property string                 $user_username   User username.
 * @property string                 $user_firstname  User first name.
 * @property string                 $user_lastname   User last name.
 * @property string                 $user_language   User language tag (IETF).
 * @method Builder whereUserNumber(int $userNumber)
 */
class User extends Model
{
    /**
     * Returns the user gamertag.
     */
    public function gamertag(): HasOne
    {
        return $this->hasOne(UserGamertag::class);
    }

    /**
     * Return the user mention (or first name) string.
     * @return null|string
     */
    public function getMention(): ?string
    {
        if ($this->user_username) {
            return '@' . $this->user_username;
        }

        return $this->user_firstname;
    }
}
