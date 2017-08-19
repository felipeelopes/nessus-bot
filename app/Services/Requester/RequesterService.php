<?php

declare(strict_types = 1);

namespace Application\Services\Requester;

use Application\Adapters\BaseFluent;
use Application\Adapters\Telegram\RequestResponse;
use Application\Exceptions\Telegram\RequestException;
use Cache;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class RequesterService
{
    const CACHE_DAY  = Carbon::MINUTES_PER_HOUR * Carbon::HOURS_PER_DAY;
    const CACHE_HOUR = Carbon::MINUTES_PER_HOUR;

    /**
     * Base URI.
     * @var string
     */
    private $baseUri;

    /**
     * Class cache base key.
     * @var string
     */
    private $classCacheBaseKey;

    /**
     * RequesterService constructor.
     * @param string $class   Class name.
     * @param string $baseUri Base URI.
     */
    public function __construct(string $class, string $baseUri)
    {
        $this->classCacheBaseKey = $class . '@';
        $this->baseUri           = $baseUri;
    }

    /**
     * Request a Bot action.
     * @param string|null $class        Fluent class.
     * @param string      $action       Action name.
     * @param array|null  $params       Action params.
     * @param int|null    $cacheMinutes Request cache (in minutes).
     * @return BaseFluent|null
     */
    public function request(?string $class, string $action, ?array $params = null, int $cacheMinutes = null): ?BaseFluent
    {
        $requestResponse = $this->requestBase($action, $params, $cacheMinutes);

        if ($requestResponse === null ||
            $class === null) {
            return null;
        }

        return new $class($requestResponse->result);
    }

    /**
     * Request a Bot action that returns an array.
     * @param string|null $class        Fluent class.
     * @param string      $action       Action name.
     * @param array|null  $params       Action params.
     * @param int|null    $cacheMinutes Request cache (in minutes).
     * @return mixed[]|null
     */
    public function requestArray(?string $class, string $action, ?array $params = null, int $cacheMinutes = null): ?array
    {
        $requestResponse = $this->requestBase($action, $params, $cacheMinutes);

        if ($requestResponse === null) {
            return null;
        }

        return array_map(function ($responseItem) use ($class) {
            return new $class($responseItem);
        }, $requestResponse->result);
    }

    /**
     * Request raw (eg. HTML).
     * @param string     $method       Method type.
     * @param string     $action       Action URL.
     * @param array|null $params       Action params.
     * @param int|null   $cacheMinutes Request cache (in minutes).
     * @return string|null
     */
    public function requestRaw(string $method, string $action, ?array $params = null, int $cacheMinutes = null): ?string
    {
        if ($cacheMinutes !== null) {
            $cacheName = $this->getCacheKey($method, $action, $params);

            if (Cache::has($cacheName)) {
                return Cache::get($cacheName);
            }
        }

        $client = new Client([
            'base_uri' => $this->baseUri,
            'timeout'  => 30.0,
        ]);

        $response         = $client->request($method, $action, $params ?? []);
        $responseContents = $response->getBody()->getContents();

        if ($cacheMinutes !== null) {
            Cache::put($this->getCacheKey($method, $action, $params), $responseContents, $cacheMinutes);
        }

        return $responseContents;
    }

    /**
     * Get the cache key.
     * @param string $method Method type.
     * @param string $action Action name.
     * @param array  $params Action params.
     * @return string
     */
    private function getCacheKey(string $method, string $action, ?array $params = null): string
    {
        return $this->classCacheBaseKey . $method . '@' . $action . ($params ? ':' . md5(json_encode($params)) : null);
    }

    /**
     * Request a base Bot action.
     * @param string     $action       Action name.
     * @param array|null $params       Action params.
     * @param int|null   $cacheMinutes Request cache (in minutes).
     * @return RequestResponse|null
     */
    private function requestBase(string $action, ?array $params = null, ?int $cacheMinutes = null): ?RequestResponse
    {
        try {
            $requestRaw = $this->requestRaw('POST', $action, [ 'query' => $params ?? [] ], $cacheMinutes);
        }
        catch (ClientException $clientException) {
            if ($clientException->getCode() === 403) {
                $response        = $clientException->getResponse();
                $requestResponse = new RequestResponse(json_decode($response->getBody()->getContents(), true));

                if ($requestResponse->description === 'Forbidden: bot was blocked by the user') {
                    return null;
                }

                throw new RequestException($requestResponse);
            }

            return null;
        }

        if ($requestRaw === null) {
            return null;
        }

        $contents = new RequestResponse(json_decode($requestRaw, true));

        if (!$contents->ok) {
            throw new RequestException($contents);
        }

        return $contents;
    }
}
