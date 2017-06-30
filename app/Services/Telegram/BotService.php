<?php

declare(strict_types = 1);

namespace Application\Services\Telegram;

use Application\Adapters\Telegram\Chat;
use Application\Adapters\Telegram\Message;
use Application\Adapters\Telegram\User;
use Application\Services\Contracts\ServiceContract;
use Application\Services\Requester\RequesterService;

class BotService implements ServiceContract
{
    /**
     * Requester Service instance.
     * @var RequesterService
     */
    private $requester;

    /**
     * BotService constructor.
     */
    public function __construct()
    {
        $this->requester = new RequesterService(__CLASS__, sprintf('https://api.telegram.org/bot%s/', env('NBOT_TOKEN')));
    }

    /**
     * @inheritdoc
     */
    public static function getInstance(): BotService
    {
        return app(static::class);
    }

    /**
     * Delete a message from group.
     * @param string|int $chatID    Chat id.
     * @param int        $messageId Message id.
     */
    public function deleteMessage($chatID, int $messageId): void
    {
        $this->requester->request(null, 'deleteMessage', [
            'chat_id'    => $chatID,
            'message_id' => $messageId,
        ]);
    }

    /**
     * Get the Chat instance from id.
     * @param string|int $chatId Chat id.
     * @return Chat
     */
    public function getChat($chatId): Chat
    {
        /** @var Chat $response */
        $response = $this->requester->request(Chat::class, 'getChat', [ 'chat_id' => $chatId ], RequesterService::CACHE_HOUR);

        return $response;
    }

    /**
     * Returns the Bot reference User.
     * @return User
     */
    public function getMe(): ?User
    {
        /** @var User $response */
        $response = $this->requester->request(User::class, 'getMe', null, RequesterService::CACHE_HOUR);

        return $response;
    }

    /**
     * Send a message to an user.
     * @param string|int $chatId      Chat id (eg. user identifier).
     * @param string     $text        Message text.
     * @param array|null $replyMarkup Reply markup.
     * @return Message|null
     */
    public function sendMessage($chatId, string $text, ?array $replyMarkup = null): ?Message
    {
        /** @var Message $response */
        $response = $this->requester->request(Message::class, 'sendMessage', array_filter([
            'chat_id'      => $chatId,
            'text'         => $text,
            'parse_mode'   => 'Markdown',
            'reply_markup' => $replyMarkup,
        ]));

        return $response;
    }

    /**
     * Send a Sticker to user.
     * @param string|int $chatId  Chat id (eg. user identifier).
     * @param string     $sticker Message sticker.
     * @return Message|null
     */
    public function sendSticker($chatId, string $sticker): ?Message
    {
        /** @var Message $response */
        $response = $this->requester->request(Message::class, 'sendSticker', [
            'chat_id'              => $chatId,
            'sticker'              => $sticker,
            'disable_notification' => true,
        ]);

        return $response;
    }
}
