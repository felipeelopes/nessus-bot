<?php

declare(strict_types = 1);

namespace Application\Services\Live;

use Application\Adapters\Live\Gamertag;
use Application\Services\Contracts\ServiceContract;
use Application\Services\MockupService;
use Application\Services\Requester\Live\RequesterService;
use GuzzleHttp\Exception\ClientException;

class LiveService implements ServiceContract
{
    /**
     * @inheritdoc
     */
    public static function getInstance(): LiveService
    {
        return MockupService::getInstance()->instance(__CLASS__);
    }

    /**
     * Check if the Gamertag does exists.
     * @param string $gamertag Gamertag name.
     * @return Gamertag|null
     */
    public function getGamertag(string $gamertag): ?Gamertag
    {
        /** @var RequesterService $requester */
        $mockupService = MockupService::getInstance();
        $requester     = $mockupService->newInstance(RequesterService::class, [ __CLASS__, 'https://xboxapi.com/v2/' ]);
        $requesterAuth = [ 'headers' => [ 'X-Auth' => env('LIVE_API_ID') ] ];

        try {
            $response = $requester->requestRaw('GET', sprintf('%s/profile', $gamertag), $requesterAuth);
        }
        catch (ClientException $clientException) {
            return null;
        }

        if (!$response) {
            return null;
        }

        $responseJson = json_decode($response, true);
        $gamertagId   = array_get($responseJson, 'id');

        if ($gamertagId === null) {
            return null;
        }

        return new Gamertag([
            'id'    => $gamertagId,
            'value' => array_get($responseJson, 'Gamertag'),
        ]);
    }
}
