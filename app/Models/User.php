<?php

declare(strict_types = 1);

namespace Application\Models;

use Application\Models\Traits\SoftDeletes;
use Application\Services\Telegram\BotService;
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
    use SoftDeletes;

    /**
     * Returns the user gamertag.
     */
    public function gamertag(): HasOne
    {
        return $this->hasOne(UserGamertag::class);
    }

    /**
     * Returns the User fullname.
     * @return string
     */
    public function getFullname(): string
    {
        return implode(' ', array_unique(array_filter([ $this->user_firstname, $this->user_lastname ])));
    }

    /**
     * Return the user mention (or first name) string.
     */
    public function getMention(): string
    {
        $botService = BotService::getInstance();

        if ($this->gamertag) {
            return $botService->formatMention($this->gamertag->gamertag_value, $this->user_number);
        }

        return $botService->formatMention($this->getFullname(), $this->user_number);
    }
}
