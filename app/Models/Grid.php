<?php

declare(strict_types = 1);

namespace Application\Models;

use Application\Adapters\Telegram\User as TelegramUser;
use Application\SessionsProcessor\GridModification\UnsubscribeMoment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as BuilderQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property Collection|GridSubscription[] $subscribers         Grid subscribers.
 * @property Collection|GridSubscription[] $subscribers_sorted  Grid subscribers (sorted).
 * @property string                        $grid_title          Grid title.
 * @property string|null                   $grid_subtitle       Grid subtitle.
 * @property string|null                   $grid_requirements   Grid requirements.
 * @property int                           $grid_players        Grid players limit.
 * @property Carbon                        $grid_timing         Grid timing to start.
 * @property Carbon                        $grid_duration       Grid duration.
 * @property string                        $grid_status         Grid status.
 * @property string                        $grid_status_details Grid status description.
 *
 * @method Builder filterAvailables()
 * @method Builder filterOpeneds()
 * @method Builder filterOwneds(User $user)
 * @method Builder filterSubscribeds(User $user)
 * @method Builder orderByTiming()
 */
class Grid extends Model
{
    public const STATUS_CANCELED   = 'canceled';
    public const STATUS_FINISHED   = 'finished';
    public const STATUS_GATHERING  = 'gathering';
    public const STATUS_PLAYING    = 'playing';
    public const STATUS_UNREPORTED = 'unreported';
    public const STATUS_WAITING    = 'waiting';

    /**
     * Model casts.
     * @var string[]
     */
    protected $casts = [
        'grid_players' => 'int',
        'grid_timing'  => 'datetime',
    ];

    /**
     * Accept a new titular-reserve, if have vacancy.
     */
    public function acceptTitularReserve()
    {
        if ($this->getVacancies() > 0) {
            $this->load([
                'subscribers',
                'subscribers_sorted',
            ]);

            /** @var GridSubscription $titularReserve */
            $titularReserve = $this->subscribers_sorted->whereIn('subscription_position', GridSubscription::POSITION_TITULAR_RESERVE)->first();

            if ($titularReserve) {
                $titularReserve->subscription_position = GridSubscription::POSITION_TITULAR;
                $titularReserve->save();
            }
        }
    }

    /**
     * Count ocurrences by rule.
     * @param string[] $positions Positions to count.
     * @return int
     */
    public function countByPosition(array $positions): int
    {
        return $this->subscribers->whereIn('subscription_position', $positions)->count();
    }

    /**
     * Count players subscribed on grid.
     * @return int
     */
    public function countPlayers(): int
    {
        return $this->countByPosition([ GridSubscription::POSITION_TITULAR ]);
    }

    /**
     * Count players reserved on grid.
     * @return int
     */
    public function countReserves(): int
    {
        return $this->countByPosition([
            GridSubscription::POSITION_TITULAR_RESERVE,
            GridSubscription::POSITION_RESERVE,
        ]);
    }

    /**
     * Return the cancel reason.
     * @return string|null
     */
    public function getCancelReason(): ?string
    {
        switch ($this->grid_status_details) {
            case UnsubscribeMoment::CANCEL_ACCESS_ISSUE:
            case UnsubscribeMoment::CANCEL_LACK_INTEREST:
            case UnsubscribeMoment::CANCEL_LACK_PLAYERS:
            case UnsubscribeMoment::CANCEL_OTHERS:
            case UnsubscribeMoment::CANCEL_PERSONAL_REASON:
                $reason = trans('GridModification.unsubscribeCancel' . Str::ucfirst($this->grid_status_details));

                return Str::lower(Str::substr($reason, 0, 1)) . Str::substr($reason, 1);
        }

        return $this->grid_status_details;
    }

    /**
     * Returns the duration as float.
     * @return float
     */
    public function getDurationAsFloat(): float
    {
        return $this->grid_duration->hour +
               ($this->grid_duration->minute / Carbon::MINUTES_PER_HOUR);
    }

    /**
     * Returns a Carbon from timing.
     * @param string $value Duration value.
     * @return Carbon
     */
    public function getGridDurationAttribute($value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        return Carbon::createFromFormat('H:i:s', $value);
    }

    /**
     * Returns the subtitle shortly.
     * @return string|null
     */
    public function getShortSubtitle(): ?string
    {
        if ($this->grid_subtitle === null) {
            return null;
        }

        if (ctype_digit($this->grid_subtitle)) {
            return $this->grid_subtitle;
        }

        if (preg_match_all('/\b\w/u', $this->grid_subtitle, $matches)) {
            return implode($matches[0]);
        }

        return $this->grid_subtitle;
    }

    /**
     * Returns the status code.
     * @return int
     */
    public function getStatusCode(): int
    {
        switch ($this->grid_status) {
            case self::STATUS_UNREPORTED:
                return 1;
                break;
            case self::STATUS_PLAYING:
                return 2;
                break;
            case self::STATUS_GATHERING:
                return 3;
                break;
            case self::STATUS_WAITING:
                return 4;
                break;
            case self::STATUS_FINISHED:
                return 5;
                break;
            case self::STATUS_CANCELED:
                return 6;
                break;
        }

        return 0;
    }

    /**
     * Return an User subscription from grid, if it exists.
     * @param TelegramUser $user User instance.
     * @return GridSubscription|null
     */
    public function getUserSubscription(TelegramUser $user): ?GridSubscription
    {
        return $this->subscribers->where('gamertag.user.user_number', $user->id)->first();
    }

    /**
     * Return the vacancies as titular.
     * @return int
     */
    public function getVacancies(): int
    {
        return $this->grid_players - $this->countPlayers();
    }

    /**
     * Return if this grid was canceled.
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->grid_status === self::STATUS_CANCELED;
    }

    /**
     * Check if an user is the manager (or owner) of this Grid.
     * @param TelegramUser $user       User instance.
     * @param bool|null    $explicitly If is explicitly the owner (not an administrator).
     * @return bool
     */
    public function isManager(TelegramUser $user, ?bool $explicitly = null): bool
    {
        if ($explicitly !== false && $this->isOwner($user)) {
            return true;
        }

        $gridSubscriber = $this->getUserSubscription($user);

        return $gridSubscriber && $gridSubscriber->subscription_rule === GridSubscription::RULE_MANAGER;
    }

    /**
     * Check if an user is the owner of this Grid.
     * @param TelegramUser $user       User instance.
     * @param bool|null    $explicitly If is explicitly the owner (not an administrator).
     * @return bool
     */
    public function isOwner(TelegramUser $user, ?bool $explicitly = null): bool
    {
        if ($explicitly !== false && $user->isAdminstrator()) {
            return true;
        }

        $gridSubscriber = $this->getUserSubscription($user);

        return $gridSubscriber && $gridSubscriber->subscription_rule === GridSubscription::RULE_OWNER;
    }

    /**
     * Identify if grid is playing.
     * @return bool
     */
    public function isPlaying(): bool
    {
        return $this->grid_status === self::STATUS_PLAYING;
    }

    /**
     * Check if an user is in fact subscribed on grid.
     * @param TelegramUser $user User instance.
     * @return bool
     */
    public function isSubscriber(TelegramUser $user): bool
    {
        return $this->getUserSubscription($user) !== null;
    }

    /**
     * Identify if grid is for today.
     * @return bool
     */
    public function isToday(): bool
    {
        return $this->grid_timing->isToday();
    }

    /**
     * Check if an user is registered with user permission on grid.
     * @param TelegramUser $user       User instance.
     * @param bool|null    $explicitly If is explicitly the owner (not an administrator).
     * @return bool
     */
    public function isUser(TelegramUser $user, ?bool $explicitly = null): bool
    {
        if ($explicitly !== false && $this->isManager($user)) {
            return true;
        }

        $gridSubscriber = $this->getUserSubscription($user);

        return $gridSubscriber && $gridSubscriber->subscription_rule === GridSubscription::RULE_USER;
    }

    /**
     * Filter for only available grids.
     * @param Builder $builder Builder instance.
     */
    public function scopeFilterAvailables(Builder $builder): void
    {
        $builder->whereIn('grid_status', [ self::STATUS_WAITING, self::STATUS_GATHERING, self::STATUS_PLAYING ]);
    }

    /**
     * Filter for only opened grids.
     * @param Builder $builder Builder instance.
     */
    public function scopeFilterOpeneds(Builder $builder): void
    {
        $this->filterAvailables();
        $builder->whereRaw("grid_timing >= TIMESTAMP(NOW(), '-00:15:00')");
    }

    /**
     * Filter for only owned opened grids.
     * @param Builder $builder Builder instance.
     * @param User    $user    User instance.
     */
    public function scopeFilterOwneds(Builder $builder, User $user): void
    {
        $builder->whereIn('id', function (BuilderQuery $builder) use ($user) {
            $builder->select('grid_id');
            $builder->from('grid_subscriptions');
            $builder->where('gamertag_id', $user->gamertag->id);
        });
    }

    /**
     * Filter for only owned opened grids.
     * @param Builder $builder Builder instance.
     * @param User    $user    User instance.
     */
    public function scopeFilterSubscribeds(Builder $builder, User $user): void
    {
        $builder->whereIn('id', function (BuilderQuery $builder) use ($user) {
            $builder->select('grid_id');
            $builder->from((new GridSubscription)->getTable());
            $builder->where('gamertag_id', $user->gamertag->id);
        });
    }

    /**
     * Sort grids by timing.
     * @param Builder $builder Builder instance.
     */
    public function scopeOrderByTiming(Builder $builder): void
    {
        $builder->orderByRaw('grid_status = ?', self::STATUS_PLAYING);
        $builder->orderBy('grid_timing');
    }

    /**
     * Returns all subscribers of list.
     * @return HasMany
     */
    public function subscribers(): HasMany
    {
        return $this->hasMany(GridSubscription::class);
    }

    /**
     * Returns all subscribers of list sorted by rule and position.
     * @return HasMany
     */
    public function subscribers_sorted(): HasMany
    {
        /** @var GridSubscription $hasMany */
        $hasMany = $this->hasMany(GridSubscription::class);

        return $hasMany->orderByGridRanking();
    }
}
