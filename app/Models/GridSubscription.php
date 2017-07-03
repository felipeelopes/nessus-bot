<?php

declare(strict_types = 1);

namespace Application\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int          $grid_id                  Grid id reference.
 * @property int          $gamertag_id              Gamertag id reference.
 * @property string|null  $subscription_description Subscription description.
 * @property string       $subscription_rule        Subscription rule (RULE consts).
 * @property string|null  $reserve_type             Reserve type (RESERVE_TYPE consts).
 * @property UserGamertag $gamertag                 Gamertag reference.
 */
class GridSubscription extends Model
{
    public const GROUP_RULE_TITULARS = [
        self::RULE_OWNER,
        self::RULE_MANAGER,
        self::RULE_TITULAR,
    ];

    public const RESERVE_TYPE_TOP  = 'top';
    public const RESERVE_TYPE_WAIT = 'wait';

    public const RULE_MANAGER = 'manager';
    public const RULE_OWNER   = 'owner';
    public const RULE_RESERVE = 'reserve';
    public const RULE_TITULAR = 'titular';

    /**
     * Returns the gamertag subscribed.
     * @return HasOne
     */
    public function gamertag(): HasOne
    {
        return $this->hasOne(UserGamertag::class, 'id', 'gamertag_id');
    }

    /**
     * Returns the subscription type icon.
     * @return null|string
     */
    public function getIcon(): ?string
    {
        if ($this->subscription_rule === self::RULE_OWNER) {
            return trans('Grid.typeOwner');
        }

        if ($this->subscription_rule === self::RULE_MANAGER) {
            return trans('Grid.typeManager');
        }

        if ($this->reserve_type === self::RESERVE_TYPE_TOP) {
            return trans('Grid.typeTop');
        }

        return null;
    }

    /**
     * Returns if subscription is titular.
     * @return bool
     */
    public function isTitular(): bool
    {
        return in_array($this->subscription_rule, self::GROUP_RULE_TITULARS, true);
    }
}
