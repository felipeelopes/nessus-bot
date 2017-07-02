<?php

declare(strict_types = 1);

namespace Tests\Mockups\Requester;

use Application\Services\MockupService;
use Application\Services\Requester\RequesterService;

class RequesterServiceMockup extends RequesterService
{
    /**
     * Mockup the request.
     * @inheritdoc
     */
    public function requestRaw(string $method, string $action, ?array $params = null, int $cacheMinutes = null): ?string
    {
        return MockupService::getInstance()->callProvider(static::class, func_get_args());
    }
}
