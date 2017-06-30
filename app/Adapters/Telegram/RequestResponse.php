<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;

/**
 * @property-read bool        $ok          Is response ok?
 * @property-read string|null $description Response description (only if not ok).
 * @property-read array|null  $result      Response result (only if ok).
 */
class RequestResponse extends BaseFluent
{
}
