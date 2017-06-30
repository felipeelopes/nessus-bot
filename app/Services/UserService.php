<?php

declare(strict_types = 1);

namespace Application\Services;

use Application\Adapters\Telegram\User as TelegramUser;
use Application\Models\User;
use Application\Services\Contracts\ServiceContract;
use Cache;
use Illuminate\Database\Eloquent\Builder;

class UserService implements ServiceContract
{
    /**
     * @inheritdoc
     */
    public static function getInstance(): UserService
    {
        return app(static::class);
    }

    /**
     * Get an User by id.
     * @param int $userId User id.
     * @return User|null
     */
    public function get($userId): ?User
    {
        return Cache::remember(__CLASS__ . '@get:' . $userId, 60, function () use ($userId) {
            /** @var User|Builder $userQuery */
            $userQuery = User::query();
            $userQuery->whereUserNumber($userId);

            return $userQuery->first();
        });
    }

    /**
     * Register a new User.
     * @param TelegramUser $telegramUser User instance to register.
     * @return User
     */
    public function register(TelegramUser $telegramUser): User
    {
        $user                 = new User;
        $user->user_number    = $telegramUser->id;
        $user->user_username  = $telegramUser->username;
        $user->user_firstname = $telegramUser->first_name;
        $user->user_lastname  = $telegramUser->last_name;
        $user->user_language  = $telegramUser->language_code;
        $user->save();

        Cache::forget(__CLASS__ . '@get:' . $telegramUser->id);

        return $user;
    }
}
