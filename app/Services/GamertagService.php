<?php

declare(strict_types = 1);

namespace Application\Services;

use Application\Models\User;
use Application\Models\UserGamertag;
use Application\Services\Contracts\ServiceContract;

class GamertagService implements ServiceContract
{
    /**
     * @inheritdoc
     */
    public static function getInstance(): GamertagService
    {
        return MockupService::getInstance()->instance(static::class);
    }

    /**
     * Check if the gamertag is valid.
     * @param string $gamertag
     * @return bool
     */
    public function isValid(string $gamertag): bool
    {
        return preg_match('/^[a-z\d\040]{1,15}$/i', $gamertag) === 1;
    }

    /**
     * Register a new Gamertag to User.
     * @param User   $user          User instance.
     * @param string $gamertagValue Gamertag value.
     * @return UserGamertag
     */
    public function register(User $user, string $gamertagValue): UserGamertag
    {
        $gamertag                 = new UserGamertag;
        $gamertag->user_id        = $user->id;
        $gamertag->gamertag_value = $gamertagValue;
        $gamertag->save();

        return $gamertag;
    }
}
