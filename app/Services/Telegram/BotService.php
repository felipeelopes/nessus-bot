<?php

declare(strict_types = 1);

namespace Application\Services\Telegram;

use Application\Adapters\BaseFluent;
use Application\Adapters\Predefinition\OptionItem;
use Application\Adapters\Telegram\Chat;
use Application\Adapters\Telegram\ChatMember;
use Application\Adapters\Telegram\Message;
use Application\Adapters\Telegram\User;
use Application\Services\CommandService;
use Application\Services\Contracts\ServiceContract;
use Application\Services\MockupService;
use Application\Services\PredefinitionService;
use Application\Services\Requester\Telegram\RequesterService;
use Cache;

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
     * Get the Chat administrators.
     * @return ChatMember[]
     */
    public function getChatAdministrators(): array
    {
        return $this->requester->requestArray(ChatMember::class, 'getChatAdministrators', [ 'chat_id' => env('NBOT_GROUP_ID') ], RequesterService::CACHE_DAY);
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
     * Send a "check on private" notification.
     * @param Message $message Message instance.
     * @return Message|null
     */
    public function notifyPrivateMessage(Message $message): ?Message
    {
        if ($message->isPrivate()) {
            return null;
        }

        $createdMessage = $this->sendMessage(
            $message->chat->id,
            trans('PublicMessages.letsToPrivate', [
                'botUsername' => '@' . $this->getMe()->username,
            ])
        );

        $previousMessageId = __CLASS__ . '@' . __FUNCTION__ . '.messageId';
        $previousMessage   = Cache::get($previousMessageId);

        /** @var Message $previousMessage */
        if ($previousMessage !== null) {
            $this->deleteMessage($previousMessage->chat->id, $previousMessage->message_id);
        }

        Cache::forever($previousMessageId, $createdMessage);

        return $createdMessage;
    }

    /**
     * Send a message to an User with the cancel option.
     * @param string|int $chatId Chat id (eg. user identifier).
     * @param string     $text   Message text.
     * @return Message|null
     */
    public function sendCancelableMessage($chatId, string $text): ?Message
    {
        return $this->sendMessage($chatId, trans('CancelCommand.textPlaceholder', [
            'text'    => $text,
            'command' => '/' . trans('Command.commands.' . CommandService::COMMAND_CANCEL . 'Command'),
        ]));
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
            'text'                     => preg_replace('/(\r?\n){3,}/', "\n\n", $text),
            'parse_mode'               => 'Markdown',
            'disable_web_page_preview' => true,
            'reply_markup'             => $replyMarkup !== null
                ? json_encode($replyMarkup)
                : null,
        ]));

        return $response;
    }

    /**
     * Send a message with specific options.
     * @param string|int  $chatId       Chat id.
     * @param string|null $text         Message text.
     * @param array       $options      Specific options.
     * @param bool|null   $isCancelable Show the cancel command (default: true).
     * @return Message|null
     */
    public function sendOptionsMessage($chatId, ?string $text, array $options, ?bool $isCancelable = null): ?Message
    {
        return $this->sendPredefinedMessageWithTemplate($chatId, $text, $options, 'Predefinition.specificOptions', $isCancelable);
    }

    /**
     * Send a message with predefined options.
     * @param string|int  $chatId       Chat id.
     * @param string|null $text         Message text.
     * @param array       $options      Predefined options.
     * @param bool|null   $isCancelable Show the cancel command (default: true).
     * @return Message|null
     */
    public function sendPredefinedMessage($chatId, ?string $text, array $options, ?bool $isCancelable = null): ?Message
    {
        return $this->sendPredefinedMessageWithTemplate($chatId, $text, $options, null, $isCancelable);
    }

    /**
     * Send a public message.
     * @param string $message Message to public.
     * @return Message|null
     */
    public function sendPublicMessage(string $message): ?Message
    {
        return $this->sendMessage(env('NBOT_GROUP_ID'), $message);
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

    /**
     * Send a predefined message with a specific template.
     * @param string|int  $chatId       Chat id.
     * @param string|null $text         Message text.
     * @param array       $options      Options list.
     * @param null|string $template     Template.
     * @param bool|null   $isCancelable Show the cancel command (default: true).
     * @return Message|null
     */
    private function sendPredefinedMessageWithTemplate($chatId, ?string $text, array $options, ?string $template = null, ?bool $isCancelable = null): ?Message
    {
        if ($isCancelable !== false) {
            $options[] = new OptionItem([ 'command' => CommandService::COMMAND_CANCEL ]);
        }

        $predefinitionService = PredefinitionService::getInstance();
        $predefinitionService->setOptions($options);

        $text .= $predefinitionService->buildOptions($template);

        return $this->sendMessage($chatId, $text);
    }
}
