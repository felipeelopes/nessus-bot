<?php

declare(strict_types = 1);

namespace Application\Services\Bungie;

use Application\Adapters\Bungie\Activity;
use Application\Adapters\Bungie\CarnageReportEntry;
use Application\Adapters\Bungie\Character;
use Application\Adapters\Bungie\GroupV2;
use Application\Adapters\Bungie\Response;
use Application\Adapters\Bungie\UserInfoCard;
use Application\Services\Contracts\ServiceContract;
use Application\Services\MockupService;
use Application\Services\Requester\Live\RequesterService;
use Application\Services\Telegram\BotService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

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
     * Get a character activities.
     * @return Collection
     * @throws \Exception
     */
    public function getCharacterActivities(int $membershipId, int $characterId, ?int $page = null, ?int $count = null, ?int $mode = null, ?bool $avoidCache = null): Collection
    {
        $page  = $page ?? 0;
        $count = $count ?? 10;
        $mode  = $mode ?? 0;

        $activitiesResponse = $this->request('GET',
            sprintf('Destiny2/1/Account/%u/Character/%u/Stats/Activities/?page=%u&count=%u&mode=%u',
                $membershipId,
                $characterId,
                $page,
                $count,
                $mode),
            null,
            $avoidCache !== true ? RequesterService::CACHE_HOUR : null);

        if ($activitiesResponse === null) {
            return new Collection;
        }

        return new Collection(array_map(function ($activity) {
            return new Activity($activity);
        }, (array) array_get($activitiesResponse, 'activities')));
    }

    /**
     * Return the user characters.
     * @return Collection|Character[]
     * @throws \Exception
     */
    public function getCharacters(int $membershipId): Collection
    {
        $charactersResponse = $this->request('GET', sprintf('Destiny2/1/Profile/%u/?components=200', $membershipId), null, RequesterService::CACHE_HOUR);

        if ($charactersResponse === null) {
            return new Collection;
        }

        return new Collection(array_map(function ($characterData) {
            return new Character($characterData);
        }, array_values(array_get($charactersResponse, 'characters.data'))));
    }

    /**
     * Get Clan details from user Membership.
     * @throws \Exception
     */
    public function getClanFromMember(int $membershipId): ?GroupV2
    {
        $statsResponse = $this->request('GET', sprintf('GroupV2/User/1/%u/0/1/', $membershipId));

        if (!$statsResponse) {
            throw new RuntimeException;
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
     * Returns the entries from a carnage report for each participating member.
     * @return Collection|CarnageReportEntry[]
     * @throws \Exception
     */
    public function getMemberCarnageReport(Activity $activityInstance): Collection
    {
        $carnageResponse = $this->request('GET', sprintf('Destiny2/Stats/PostGameCarnageReport/%u/', $activityInstance->instanceId), null, RequesterService::CACHE_DAY);

        if ($carnageResponse === null) {
            return new Collection;
        }

        $entries = new Collection(array_map(function ($activity) use ($activityInstance) {
            return new CarnageReportEntry($activity, $activityInstance);
        }, (array) array_get($carnageResponse, 'entries')));

        return $entries->groupBy(function (CarnageReportEntry $carnageReportEntry) {
            return $carnageReportEntry->membershipId;
        })->map(function (Collection $memberEntries) {
            $firstEntry = $memberEntries->shift();

            return $memberEntries->reduce(function (CarnageReportEntry $carry, CarnageReportEntry $nextEntry) {
                return $carry->mergeWith($nextEntry);
            }, $firstEntry);
        });
    }

    /**
     * Request a Bungie API action.
     * @return mixed|null
     * @throws \Exception
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

        if ($requestResponse->ErrorStatus === 'DestinyUnexpectedError') {
            $botService = BotService::getInstance();
            $botService->createMessage($botService->getUpdate()->message)
                ->appendMessage(trans('EdgeCommand.exceptionServerMaintenance'))
                ->publish();

            exit;
        }

        if ($requestResponse->Message !== 'Ok') {
            return null;
        }

        return $requestResponse->Response;
    }

    /**
     * Get user info card from gamertag.
     * @param string $gamertag Gamertag name.
     * @throws \Exception
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
     * @throws \Exception
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
