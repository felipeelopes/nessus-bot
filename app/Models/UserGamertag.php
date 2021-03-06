<?php

declare(strict_types = 1);

namespace Application\Models;

use Application\Models\Observers\UserGamertagObserver;
use Application\Models\Traits\LastTouchBeforeFilter;
use Application\Models\Traits\SoftDeletes;
use Application\Services\Bungie\BungieService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @property User|null $user              User referenced.
 * @property int       $user_id           User referenced id.
 * @property int|null  $gamertag_id       Gamergag id.
 * @property string    $gamertag_value    Gamertag value.
 * @property int|null  $bungie_membership Bungie membership.
 * @property int|null  $bungie_clan       Bungie clan.
 *
 * @method $this filterBySimilarity(string $gamertag)
 * @method $this orderBySimilarity(string $gamertag)
 */
class UserGamertag extends Model
{
    use SoftDeletes,
        LastTouchBeforeFilter;

    /**
     * Model boot.
     */
    protected static function boot(): void
    {
        static::observe(UserGamertagObserver::class);

        parent::boot();
    }

    /**
     * Returns the gamertag related clan.
     * @return null|string
     */
    public function getClan(?bool $skipNonAllied = null): ?string
    {
        if (!$this->bungie_clan) {
            return null;
        }

        if ($skipNonAllied === true &&
            !in_array($this->bungie_clan, explode(',', env('NBOT_CLANS')))) {
            return null;
        }

        $bungieService = BungieService::getInstance();
        $bungieClan    = $bungieService->getClan($this->bungie_clan);

        if (!$bungieClan) {
            return null;
        }

        return $bungieClan->clanCallsign;
    }

    /**
     * Find user by similarity.
     * @param Builder $builder  Builder instance.
     * @param string  $gamertag Gamertag to find.
     */
    public function scopeFilterBySimilarity(Builder $builder, string $gamertag)
    {
        $builder->where('gamertag_value', 'LIKE', "%{$gamertag}%");
    }

    /**
     * Find user by similarity.
     * @param Builder $builder  Builder instance.
     * @param string  $gamertag Gamertag to find.
     */
    public function scopeOrderBySimilarity(Builder $builder, string $gamertag)
    {
        $builder->orderByRaw('LEVENSHTEIN_RATIO(LOWER(`gamertag_value`), ?) DESC', [
            Str::lower($gamertag),
        ]);
    }

    /**
     * Return the referenced user.
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
