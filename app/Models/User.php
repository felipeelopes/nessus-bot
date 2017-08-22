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
     * @param bool|null $fullname Return with fullname (default: false).
     * @return null|string
     */
    public function getMention(?bool $fullname = null): ?string
    {
        if ($this->user_username) {
            return '@' . addcslashes($this->user_username, '_');
        }

        if ($fullname === true) {
            return trim($this->user_firstname . ' ' . $this->user_lastname);
        }

        return $this->user_firstname;
    }
}
