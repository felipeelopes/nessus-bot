<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;
use Application\Models\User as UserModel;
use Application\Services\MockupService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;

/**
 * @property-read int         $id            User id.
 * @property-read string      $first_name    User first name.
 * @property-read string|null $last_name     User last name.
 * @property-read string|null $username      User username.
 * @property-read string|null $language_code User language code tag (IETF).
 * @property-read bool        $is_bot        User is bot?
 */
class User extends BaseFluent
{
    /**
     * Returns the fullname of User.
     * @return string
     */
    public function getFullname(): string
    {
        return implode(' ', array_filter([ $this->first_name, $this->last_name ]));
    }

    /**
     * Return the user mention or fullname.
     * @return string
     */
    public function getMention(): string
    {
        $botService   = BotService::getInstance();
        $userRegister = $this->getUserRegister();

        if ($userRegister && $userRegister->gamertag) {
            return $botService->formatMention($userRegister->gamertag->gamertag_value, $this->id);
        }

        return $botService->formatMention($this->getFullname(), $this->id);
    }

    /**
     * Returns the user register.
     * @return UserModel|null
     */
    public function getUserRegister(): ?UserModel
    {
        /** @var UserService $userService */
        $userService = MockupService::getInstance()->instance(UserService::class);

        return $userService->get($this->id);
    }

    /**
     * Check if this user is a group administrator.
     * @return bool
     */
    public function isAdminstrator(): bool
    {
        $botService        = BotService::getInstance();
        $administratorsIds = array_pluck($botService->getChatAdministrators(), 'user.id');

        return in_array($this->id, $administratorsIds, true);
    }
}
