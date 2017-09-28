<?php

declare(strict_types = 1);

namespace Application\Services\Bungie;

use Application\Adapters\Bungie\GroupV2;
use Application\Adapters\Bungie\Response;
use Application\Adapters\Bungie\UserInfoCard;
use Application\Services\Contracts\ServiceContract;
use Application\Services\MockupService;
use Application\Services\Requester\Live\RequesterService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
     * Get Clan details from user Membership.
     */
    public function getClanFromMember(int $membershipId): ?GroupV2
    {
        $statsResponse = $this->request('GET', sprintf('GroupV2/User/1/%u/0/1/', $membershipId));

        if (!$statsResponse) {
            return null;
        }

        $groupDetails = array_get($statsResponse, 'results.0');

        if (!$groupDetails) {
            return null;
        }

        return new GroupV2(array_replace(
            array_get($groupDetails, 'group'),
            [
                'joinDate'     => new Carbon(array_get($groupDetails, 'member.joinDate')),
                'clanCallsign' => array_get($groupDetails, 'group.clanInfo.clanCallsign'),
            ]
        ));
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

        if ($response === null) {
            return null;
        }

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

    /**
     * Returns the user stats (simplified version).
     */
    public function userStatsSimplified(int $membership): ?Collection
    {
        $statsResponse = $this->request('GET', sprintf('Destiny2/1/Account/%u/Stats/', $membership));

        if (!$statsResponse) {
            return null;
        }

        return (new Collection(array_get($statsResponse, 'mergedAllCharacters.results.allPvE.allTime')))->map(function ($stat) {
            return array_get($stat, 'basic.value');
        });
    }
}
