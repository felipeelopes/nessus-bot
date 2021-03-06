<?php

declare(strict_types = 1);

namespace Application\Models;

use Application\Models\Observers\UserObserver;
use Application\Models\Traits\LastTouchBeforeFilter;
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
 *
 * @method Builder whereUserNumber(int $userNumber)
 */
class User extends Model
{
    use SoftDeletes,
        LastTouchBeforeFilter;

    /**
     * Model boot.
     */
    protected static function boot(): void
    {
        static::observe(UserObserver::class);

        parent::boot();
    }

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
    public function getMention(?bool $useFullname = null): string
    {
        $botService = BotService::getInstance();

        if ($useFullname !== true && $this->gamertag) {
            return $botService->formatMention($this->gamertag->gamertag_value, $this->user_number);
        }

        return $botService->formatMention($this->getFullname(), $this->user_number);
    }

    /**
     * Check if this user is a group administrator.
     * @return bool
     */
    public function isAdminstrator(): bool
    {
        $botService        = BotService::getInstance();
        $administratorsIds = array_pluck($botService->getChatAdministrators(), 'user.id');

        return in_array($this->user_number, $administratorsIds, true);
    }
}
