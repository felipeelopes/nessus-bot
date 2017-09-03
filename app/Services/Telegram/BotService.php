<?php

declare(strict_types = 1);

namespace Application\Services\Telegram;

use Application\Adapters\BaseFluent;
use Application\Adapters\Telegram\Chat;
use Application\Adapters\Telegram\ChatMember;
use Application\Adapters\Telegram\Message;
use Application\Adapters\Telegram\SendMessage;
use Application\Adapters\Telegram\Update;
use Application\Adapters\Telegram\User;
use Application\Services\Contracts\ServiceContract;
use Application\Services\MockupService;
use Application\Services\Requester\Telegram\RequesterService;
use Illuminate\Http\Request;

class BotService implements ServiceContract
{
    /**
     * @var Update
     */
    private $currentUpdate;

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
        $mockupService   = MockupService::getInstance();
        $this->requester = $mockupService->newInstance(RequesterService::class, [ __CLASS__, sprintf('https://api.telegram.org/bot%s/', env('NBOT_TOKEN')) ]);
    }

    /**
     * @inheritdoc
     */
    public static function getInstance(): BotService
    {
        return MockupService::getInstance()->instance(static::class);
    }

    /**
     * Creates a new message service.
     * @param Message|null $message Message instance from Update.
     * @return BotMessageService
     */
    public function createMessage(?Message $message = null): BotMessageService
    {
        return new BotMessageService($message);
    }

    /**
     * Delete a message from group.
     * @param Message $message Message instace.
     */
    public function deleteMessage(Message $message): void
    {
        $this->requester->request(null, 'deleteMessage', [
            'chat_id'    => $message->chat->id,
            'message_id' => $message->message_id,
        ]);
    }

    /**
     * Delete the reply markup from message.
     * @param Message $message Message instance.
     */
    public function deleteReplyMarkup(Message $message): void
    {
        $this->requester->request(null, 'editMessageReplyMarkup', [
            'chat_id'    => $message->chat->id,
            'message_id' => $message->message_id,
        ]);
    }

    /**
     * Escape a message to be compatible with Telegram.
     * @param string|null $message Message.
     * @return string
     */
    public function escape(?string $message): string
    {
        return addcslashes((string) $message, '_*`[');
    }

    /**
     * Format a User mention.
     * @param string $mention Mention text.
     * @param int    $id      User id.
     * @return string
     */
    public function formatMention($mention, $id): string
    {
        return sprintf('[%s](tg://user?id=%u)', $this->escape($mention), $id);
    }

    /**
     * Get the Chat instance.
     * @return Chat
     */
    public function getChat(): Chat
    {
        /** @var Chat $response */
        $response = $this->requester->request(Chat::class, 'getChat', [
            'chat_id' => env('NBOT_GROUP_ID'),
        ], RequesterService::CACHE_HOUR);

        return $response;
    }

    /**
     * Get the Chat administrators.
     * @return ChatMember[]
     */
    public function getChatAdministrators(): array
    {
        return $this->requester->requestArray(ChatMember::class, 'getChatAdministrators', [
            'chat_id' => env('NBOT_GROUP_ID'),
        ], RequesterService::CACHE_DAY);
    }

    /**
     * Returns the Bot reference User.
     * @return User|BaseFluent
     */
    public function getMe(): User
    {
        return $this->requester->request(User::class, 'getMe', null, RequesterService::CACHE_HOUR);
    }

    /**
     * Returns the current Update instance.
     * @return Update
     */
    public function getUpdate(): Update
    {
        if ($this->currentUpdate) {
            return $this->currentUpdate;
        }

        /** @var Request $request */
        $request = app('request');

        $this->currentUpdate = new Update(json_decode($request->getContent(), true));

        return $this->currentUpdate;
    }

    /**
     * Publish a message based on message service.
     * @param SendMessage $message Message to send.
     * @return Message|null
     */
    public function publishMessage(SendMessage $message): ?Message
    {
        return $this->requester->request(Message::class, 'sendMessage', $message->toArray());
    }

    /**
     * Send a Sticker to public.
     * @param string $sticker Message sticker.
     * @return Message|null
     */
    public function sendPublicSticker(string $sticker): ?Message
    {
        return $this->sendSticker(env('NBOT_GROUP_ID'), $sticker);
    }

    /**
     * Send a Sticker to user.
     * @param string|int $chatId  Chat id (eg. user identifier).
     * @param string     $sticker Message sticker.
     * @return Message|null
     */
    public function sendSticker($chatId, string $sticker): ?Message
    {
        return $this->requester->request(Message::class, 'sendSticker', [
            'chat_id'              => $chatId,
            'sticker'              => $sticker,
            'disable_notification' => true,
        ]);
    }
}
