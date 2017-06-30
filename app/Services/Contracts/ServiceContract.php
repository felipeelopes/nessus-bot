<?php

declare(strict_types = 1);

namespace Application\Services\Contracts;

interface ServiceContract
{
    /**
     * Get the SessionService instance.
     * @return mixed
     */
    public static function getInstance();
}
