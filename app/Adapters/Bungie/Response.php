<?php

declare(strict_types = 1);

namespace Application\Adapters\Bungie;

use Application\Adapters\BaseFluent;

/**
 * @property mixed  $Response    Response data.
 * @property int    $ErrorCode   Error code.
 * @property string $ErrorStatus Error status.
 * @property string $Message     Response message.
 */
class Response extends BaseFluent
{
}
