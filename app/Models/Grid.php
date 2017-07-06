<?php

declare(strict_types = 1);

namespace Application\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * @property Collection|GridSubscription[] $subscribers         Grid subscribers.
 * @property UserGamertag                  $gamertag            Owner Gamertag instance.
 * @property int                           $gamertag_id         Owner Gamertag id.
 * @property string                        $grid_title          Grid title.
 * @property string|null                   $grid_subtitle       Grid subtitle.
 * @property string|null                   $grid_requirements   Grid requirements.
 * @property int                           $grid_players        Grid players limit.
 * @property Carbon                        $grid_timing         Grid timing to start.
 * @property string                        $grid_status         Grid status.
 * @property string                        $grid_status_details Grid status description.
 *
 * @method Builder filterOpeneds()
 * @method Builder orderByTiming()
 */
class  Grid extends Model
{
    public const STATUS_CANCELED  = 'canceled';
    public const STATUS_FINISHED  = 'finished';
    public const STATUS_GATHERING = 'gathering';
    public const STATUS_PLAYING   = 'playing';
    public const STATUS_WAITING   = 'waiting';

    /**
     * Model casts.
     * @var string[]
     */
    protected $casts = [
        'grid_players' => 'int',
        'grid_timing'  => 'datetime',
    ];

    /**
     * Count ocurrences by rule.
     * @param string[] $rules Rules to count.
     * @return int
     */
    public function countByRule(array $rules): int
    {
        return $this->subscribers->whereIn('subscription_rule', $rules)->count();
    }

    /**
     * Count players subscribed on grid.
     * @return int
     */
    public function countPlayers(): int
    {
        return $this->countByRule(GridSubscription::GROUP_RULE_TITULARS);
    }

    /**
     * Count players reserved on grid.
     * @return int
     */
    public function countReserves(): int
    {
        return $this->countByRule([
            GridSubscription::RULE_RESERVE,
        ]);
    }

    /**
     * Owner Gamertag related.
     * @return HasOne
     */
    public function gamertag(): HasOne
    {
        return $this->hasOne(UserGamertag::class);
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
     * Identify if grid is playing.
     * @return bool
     */
    public function isPlaying(): bool
    {
        return $this->grid_status === self::STATUS_PLAYING;
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
     * Filter for only opened grids.
     * @param Builder $builder Builder instance.
     */
    public function scopeFilterOpeneds(Builder $builder): void
    {
        $builder->whereIn('grid_status', [ self::STATUS_WAITING, self::STATUS_GATHERING, self::STATUS_PLAYING ]);
        $builder->whereRaw("grid_timing >= TIMESTAMP(NOW(), '-00:15:00')");
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
}
