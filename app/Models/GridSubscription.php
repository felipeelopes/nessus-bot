<?php

declare(strict_types = 1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int          $grid_id                  Grid id reference.
 * @property int          $gamertag_id              Gamertag id reference.
 * @property string|null  $subscription_description Subscription description.
 * @property string       $subscription_rule        Subscription rule (RULE consts).
 * @property string       $subscription_position    Subscription position (POSITION consts).
 * @property string|null  $reserve_type             Reserve type (RESERVE_TYPE consts).
 * @property UserGamertag $gamertag                 Gamertag reference.
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
     * Returns if subscription is titular.
     * @return bool
     */
    public function isTitular(): bool
    {
        return $this->subscription_position === self::POSITION_TITULAR;
    }
}
