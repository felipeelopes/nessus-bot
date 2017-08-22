<?php

declare(strict_types = 1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @property User|null $user           User referenced.
 * @property int       $user_id        User referenced id.
 * @property int       $gamertag_id    Gamergag id.
 * @property string    $gamertag_value Gamertag value.
 *
 * @method $this filterBySimilarity(string $gamertag)
 * @method $this orderBySimilarity(string $gamertag)
 */
class UserGamertag extends Model
{
    /**
     * Find user by similarity.
     * @param Builder $builder  Builder instance.
     * @param string  $gamertag Gamertag to find.
     */
    public function scopeFilterBySimilarity(Builder $builder, string $gamertag)
    {
        $builder->whereRaw('LEVENSHTEIN_RATIO(LOWER(`gamertag_value`), ?) >= 70', [
            Str::lower($gamertag),
        ]);
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
