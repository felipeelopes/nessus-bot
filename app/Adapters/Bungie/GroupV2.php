<?php

declare(strict_types = 1);

namespace Application\Adapters\Bungie;

use Application\Adapters\BaseFluent;
use Carbon\Carbon;

/**
 * @property-read int    $groupId      Group ID.
 * @property-read string $name         Group name.
 * @property-read string $clanCallsign Group call sign.
 * @property-read Carbon $joinDate     Member join date.
 */
class GroupV2 extends BaseFluent
{
}
