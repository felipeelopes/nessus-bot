<?php

declare(strict_types = 1);

namespace Application\Strategies\Contracts;

use Application\Adapters\Telegram\Update;
use Application\Models\User;

interface UserStrategyContract
{
    /**
     * Process an Update instance with an User.
     * @param User|null $user   User instance.
     * @param Update    $update Generic Update instance.
     * @return bool|null
     */
    public function process(?User $user, Update $update): ?bool;
}
