<?php

declare(strict_types = 1);

namespace Application\Strategies\Contracts;

use Application\Adapters\Telegram\Update;

interface UpdateStrategyContract
{
    /**
     * Process an Update instance.
     * @param Update $update Generic Update instance.
     * @return bool|null
     */
    public function process(Update $update): ?bool;
}
