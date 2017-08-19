<?php

declare(strict_types = 1);

namespace Application\Adapters\Telegram;

use Application\Adapters\BaseFluent;
use Application\Models\User;
use Illuminate\Support\Collection;

/**
 * @property-read string                     $bot       Bot name.
 * @property-read string                     $command   Bot command.
 * @property-read string                     $text      Bot command full text.
 * @property-read string[]                   $arguments Bot command arguments.
 * @property-read MessageEntity[]|Collection $entities  Bot additional entities.
 */
class MessageEntityBotCommand extends BaseFluent
{
    /**
     * @inheritdoc
     */
    public function __construct($attributes = null)
    {
        parent::__construct($attributes);

        $this->instantiateCollection('entities', MessageEntity::class);
    }

    /**
     * Return the mentions.
     * @return User[]
     */
    public function getMentions(): array
    {
        $users = [];

        foreach ($this->entities as $entity) {
            if ($entity->isType(MessageEntity::TYPE_MENTION)) {
                $username = substr($entity->getContent(), 1);

                $usersQuery = User::query();
                $usersQuery->where('user_username', $username);
                $usersQuery->has('gamertag');

                $user = $usersQuery->first();

                // Fake user.
                if (!$user) {
                    $user                = new User;
                    $user->user_username = $username;
                }

                $users[] = $user;
                continue;
            }

            if ($entity->isType(MessageEntity::TYPE_TEXT_MENTION)) {
                $usersQuery = User::query();
                $usersQuery->where('user_number', $entity->user->id);
                $usersQuery->has('gamertag');

                $user = $usersQuery->first();

                // Fake user.
                if (!$user) {
                    $user                 = new User;
                    $user->user_number    = $entity->user->id;
                    $user->user_firstname = $entity->user->first_name;
                    $user->user_lastname  = $entity->user->last_name;
                }

                $users[] = $user;
            }
        }

        return $users;
    }
}
