<?php

declare(strict_types = 1);

namespace Application\Models;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int          $grid_id                  Grid id reference.
 * @property int          $gamertag_id              Gamertag id reference.
 * @property string|null  $subscription_description Subscription description.
 * @property string       $subscription_rule        Subscription rule (RULE consts).
 * @property string       $subscription_position    Subscription position (POSITION consts).
 * @property string|null  $reserve_type             Reserve type (RESERVE_TYPE consts).
 * @property UserGamertag $gamertag                 Gamertag reference.
 *
 * @method orderByGridRanking()
 * @method orderByGridRule()
 * @method orderByGamertag()
 */
class GridSubscription extends Model
{
    public const POSITION_RESERVE_BOTTOM = 'reserveBottom';
    public const POSITION_RESERVE_TOP    = 'reserveTop';
    public const POSITION_TITULAR        = 'titular';

    public const RULE_MANAGER = 'manager';
    public const RULE_OWNER   = 'owner';
    public const RULE_USER    = 'user';

    /**
     * Returns the gamertag subscribed.
     * @return HasOne
     */
    public function gamertag(): HasOne
    {
        return $this->hasOne(UserGamertag::class, 'id', 'gamertag_id');
    }

    /**
     * Returns the subscription type icons.
     * @return string[]
     */
    public function getIcons(): array
    {
        $icons = [];

        if ($this->subscription_position === self::POSITION_RESERVE_TOP) {
            $icons[] = trans('Grid.typeTop');
        }

        if ($this->subscription_rule === self::RULE_OWNER) {
            $icons[] = trans('Grid.typeOwner');
        }
        else if ($this->subscription_rule === self::RULE_MANAGER) {
            $icons[] = trans('Grid.typeManager');
        }

        return $icons;
    }

    /**
     * Returns if subscriver is a manager.
     * @return bool
     */
    public function isManager(): bool
    {
        return $this->subscription_rule === self::RULE_MANAGER;
    }

    /**
     * Returns if subscription is titular.
     * @return bool
     */
    public function isTitular(): bool
    {
        return $this->subscription_position === self::POSITION_TITULAR;
    }

    /**
     * Order subscribers by gamertag.
     * @param Builder $builder Builder instance.
     */
    public function scopeOrderByGamertag(Builder $builder): void
    {
        $gamertagsTable = (new UserGamertag)->getTable();
        $selfTable      = DB::getTablePrefix() . (new self)->getTable();

        $gamertagsQuery = UserGamertag::query();
        $gamertagsQuery->select("{$gamertagsTable}.gamertag_value");
        $gamertagsQuery->where("{$gamertagsTable}.id", DB::raw("`{$selfTable}`.`gamertag_id`"));

        $builder->orderByRaw('(' . $gamertagsQuery->toSql() . ')');
    }

    /**
     * Order subscribers by ranking: rule and position on grid.
     * @param Builder $builder Builder instance.
     */
    public function scopeOrderByGridRanking(Builder $builder): void
    {
        // For titulars, keep rule ordered: owner, managers, then users.
        $builder->orderByRaw('
            IF(
                `subscription_position` = ?,
                FIND_IN_SET(`subscription_rule`, ?),
                NULL
            )
        ', [
            self::POSITION_TITULAR,
            implode(',', [ self::RULE_OWNER, self::RULE_MANAGER, self::RULE_USER ]),
        ]);

        // For reserves, keep position ordered: reserveTops, then reserveBottoms.
        // Then order by subscription reserve timestamp.
        $builder->orderByRaw('
            IF(
                `subscription_position` <> ?,
                FIND_IN_SET(`subscription_position`, ?),
                NULL
            ),
            IF(
                `subscription_position` <> ?,
                `reserved_at`,
                NULL
            )
        ', [
            self::POSITION_TITULAR,
            implode(',', [ self::POSITION_RESERVE_TOP, self::POSITION_RESERVE_BOTTOM ]),
            self::POSITION_TITULAR,
        ]);
    }

    /**
     * Order subscribers by rule: owner, managers, then users.
     * @param Builder $builder Builder instance.
     */
    public function scopeOrderByGridRule(Builder $builder): void
    {
        $builder->orderByRaw('FIND_IN_SET(`subscription_rule`, ?)', [
            implode(',', [ self::RULE_OWNER, self::RULE_MANAGER, self::RULE_USER ]),
        ]);
    }
}
