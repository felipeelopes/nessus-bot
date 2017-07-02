<?php

declare(strict_types = 1);

namespace Application\Services\Telegram;

use Application\Adapters\BaseFluent;
use Application\Adapters\Telegram\Chat;
use Application\Adapters\Telegram\InlineKeyboardButton;
use Application\Adapters\Telegram\Message;
use Application\Adapters\Telegram\User;
use Application\Services\Contracts\ServiceContract;
use Application\Services\MockupService;
use Application\Services\Requester\RequesterService;

class BotService implements ServiceContract
{
    public const QUERY_CANCEL = 'QueryCancel';

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
        return MockupService::getInstance()->instance(static::class);
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
     * @return User|BaseFluent
     */
    public function getMe(): ?User
    {
        return $this->requester->request(User::class, 'getMe', null, RequesterService::CACHE_HOUR);
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
            'chat_id'                  => $chatId,
            'text'                     => $text,
            'parse_mode'               => 'Markdown',
            'disable_web_page_preview' => true,
            'reply_markup'             => $replyMarkup !== null
                ? json_encode($replyMarkup)
                : null,
        ]));

        return $response;
    }

    /**
     * Send a message to an User with the cancel option.
     * @param string|int $chatId Chat id (eg. user identifier).
     * @param string     $text   Message text.
     * @return Message|null
     */
    public function sendMessageCancelable($chatId, string $text): ?Message
    {
        $inlineKeyboardButton                = new InlineKeyboardButton;
        $inlineKeyboardButton->text          = 'Cancelar';
        $inlineKeyboardButton->callback_data = self::QUERY_CANCEL;

        return $this->sendMessage($chatId, $text, [
            'inline_keyboard' => [ [ $inlineKeyboardButton->toArray() ] ],
        ]);
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
