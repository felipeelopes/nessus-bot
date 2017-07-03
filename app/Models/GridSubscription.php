<?php

declare(strict_types = 1);

namespace Application\Models;

class GridSubscription extends Model
{
    public const RESERVE_TYPE_TOP  = 'top';
    public const RESERVE_TYPE_WAIT = 'wait';

    public const RULE_MANAGER = 'manager';
    public const RULE_OWNER   = 'owner';
    public const RULE_RESERVE = 'reserve';
    public const RULE_TITULAR = 'titular';
}
