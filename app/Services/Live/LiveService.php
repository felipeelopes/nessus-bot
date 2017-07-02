<?php

declare(strict_types = 1);

namespace Application\Services\Live;

use Application\Services\Contracts\ServiceContract;
use Application\Services\MockupService;
use Application\Services\Requester\RequesterService;

class LiveService implements ServiceContract
{
    /**
     * @inheritdoc
     */
    public static function getInstance(): LiveService
    {
        return MockupService::getInstance()->instance(LiveService::class);
    }

    /**
     * Check if the Gamertag does exists.
     * @param string $gamertag Gamertag name.
     * @return bool
     */
    public function gamertagExists(string $gamertag): bool
    {
        $requester     = new RequesterService(__CLASS__, 'https://xboxapi.com/v2/');
        $requesterAuth = [ 'headers' => [ 'X-Auth' => env('LIVE_API_ID') ] ];
        $response      = $requester->requestRaw('GET', sprintf('%s/profile', $gamertag), $requesterAuth, RequesterService::CACHE_HOUR);

        if (!$response) {
            return false;
        }

        $responseJson = json_decode($response, true);

        return array_get($responseJson, 'id') !== null;
    }
}
