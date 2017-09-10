<?php

declare(strict_types = 1);

namespace Application\Services\Bungie;

use Application\Adapters\Bungie\Response;
use Application\Adapters\Bungie\UserInfoCard;
use Application\Services\Contracts\ServiceContract;
use Application\Services\MockupService;
use Application\Services\Requester\Live\RequesterService;

class BungieService implements ServiceContract
{
    /**
     * @inheritdoc
     */
    public static function getInstance(): BungieService
    {
        return MockupService::getInstance()->instance(__CLASS__);
    }

    /**
     * Request a Bungie API action.
     * @return mixed|null
     */
    public function request(string $method, string $action, ?array $params = null, int $cacheMinutes = null)
    {
        /** @var RequesterService $requester */
        $mockupService = MockupService::getInstance();
        $requester     = $mockupService->newInstance(RequesterService::class, [ __CLASS__, 'https://www.bungie.net/Platform/' ]);
        $response      = $requester->requestRaw($method, $action, [
            'headers' => [ 'X-API-Key' => env('BUNGIE_API_ID') ],
            'query'   => $params,
        ], $cacheMinutes);

        $requestResponse = new Response(json_decode($response, true));

        if ($requestResponse->Message !== 'Ok') {
            return null;
        }

        return $requestResponse->Response;
    }

    /**
     * Get user info card from gamertag.
     * @param string $gamertag Gamertag name.
     */
    public function searchUser(string $gamertag): ?UserInfoCard
    {
        $userResponse = $this->request('GET', sprintf('Destiny2/SearchDestinyPlayer/1/%s/', $gamertag), null, RequesterService::CACHE_DAY);

        if (!$userResponse) {
            return null;
        }

        return new UserInfoCard(array_first($userResponse));
    }
}
