<?php

declare(strict_types = 1);

namespace Tests\Mockups\Requester\Telegram;

use Application\Adapters\Telegram\RequestResponse;
use Application\Services\MockupService;
use Tests\Mockups\Requester\RequesterServiceMockup as RequesterServiceMockupBase;

class RequesterServiceMockup extends RequesterServiceMockupBase
{
    /**
     * Mockup the request.
     * @inheritdoc
     */
    public function requestRaw(string $method, string $action, ?array $params = null, int $cacheMinutes = null): ?string
    {
        if ($action === 'getMe') {
            $request = new RequestResponse([
                'ok'     => true,
                'result' => [
                    'id'         => env('NBOT_WEBHOOK_ID'),
                    'first_name' => 'Bot Name',
                    'username'   => 'Bot',
                ],
            ]);

            return $request->toJson();
        }

        return MockupService::getInstance()->callProvider(static::class, func_get_args());
    }
}
