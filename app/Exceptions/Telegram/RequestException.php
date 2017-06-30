<?php

declare(strict_types = 1);

namespace Application\Exceptions\Telegram;

use Application\Adapters\Telegram\RequestResponse;
use RuntimeException;

class  RequestException extends RuntimeException
{
    /**
     * RequestException constructor.
     * @param RequestResponse $requestResponse Request Response with the failure.
     */
    public function __construct(RequestResponse $requestResponse)
    {
        parent::__construct(sprintf('Request Failure (%s)', $requestResponse->description));
    }
}
