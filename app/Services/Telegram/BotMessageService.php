<?php

declare(strict_types = 1);

namespace Application\Services\Telegram;

use Application\Adapters\Predefinition\OptionItem;
use Application\Adapters\Telegram\Message;
use Application\Adapters\Telegram\SendMessage;
use Application\Services\CommandService;
use Application\Services\PredefinitionService;
use Application\Services\Requester\RequesterService;
use Cache;

class BotMessageService
{
    /**
     * @var bool|null
     */
    private $isCancelable;

    /**
     * @var SendMessage
     */
    private $message;

    /**
     * @var OptionItem[]
     */
    private $options = [];

    /**
     * @var bool|null
     */
    private $optionsSpecifics;

    /**
     * @var string
     */
    private $unduplicateIdentifier;

    /**
     * @var Message
     */
    private $updateMessage;

    /**
     * Constructor.
     * @param Message $updateMessage Message instace from Update.
     */
    public function __construct(Message $updateMessage)
    {
        $this->updateMessage = $updateMessage;

        $this->message                           = new SendMessage;
        $this->message->parse_mode               = SendMessage::PARSE_MODE_MARKDOWN;
        $this->message->disable_web_page_preview = true;

        if (self::isPublicMessage($updateMessage)) {
            $this->setReplica();
        }

        $this->setReceiver($updateMessage->chat->id);
    }

    /**
     *
     * @param Message $updateMessage
     * @return bool
     */
    private static function isPublicMessage(Message $updateMessage): bool
    {
        return $updateMessage->chat->id === (int) env('NBOT_GROUP_ID');
    }

    /**
     * Append a message text.
     * @param string $message Message text.
     * @return BotMessageService
     */
    public function appendMessage(string $message): BotMessageService
    {
        $this->message->text .= $message;

        return $this;
    }

    /**
     * Force that this message be send to public chat.
     * @return BotMessageService
     */
    public function forcePublic(): BotMessageService
    {
        $this->setReceiver(env('NBOT_GROUP_ID'));
        $this->setReplica(false);

        return $this;
    }

    /**
     * Publish the message.
     * @return Message|null
     */
    public function publish(): ?Message
    {
        if ($this->options ||
            $this->isCancelable) {
            if ($this->isCancelable) {
                $this->options[] = new OptionItem([ 'command' => CommandService::COMMAND_CANCEL ]);
            }

            $predefinitionService = PredefinitionService::getInstance();
            $predefinitionService->setOptions($this->options);

            $optionsMessage  = $predefinitionService->buildOptions();
            $optionsTemplate = $this->optionsSpecifics !== true
                ? 'Predefinition.listOptions'
                : 'Predefinition.specificOptions';

            $this->appendMessage(trans($optionsTemplate, [ 'options' => $optionsMessage ]));
        }

        $botService = BotService::getInstance();
        $message    = $botService->publishMessage($this->message);

        if ($this->unduplicateIdentifier && $message) {
            /** @var Message $previousMessageId */
            $previousMessageId = Cache::get($this->unduplicateIdentifier);

            if ($previousMessageId) {
                $botService->deleteMessage($previousMessageId);
            }

            Cache::put($this->unduplicateIdentifier, $message->onlyReference(), RequesterService::CACHE_DAY);
        }

        return $message;
    }

    /**
     * Add the cancelable option on this message.
     * @return BotMessageService
     */
    public function setCancelable(): BotMessageService
    {
        $this->isCancelable = true;

        return $this;
    }

    /**
     * Set options to this message.
     * @param OptionItem[] $options         Options list.
     * @param bool|null    $specificOptions Determine that this list have specific options.
     * @return BotMessageService
     */
    public function setOptions(array $options, ?bool $specificOptions = null): BotMessageService
    {
        $this->options          = $options;
        $this->optionsSpecifics = $specificOptions;

        return $this;
    }

    /**
     * Set this message as private.
     * @return $this
     */
    public function setPrivate(): BotMessageService
    {
        $this->setReceiver($this->updateMessage->from->id);

        if (self::isPublicMessage($this->updateMessage)) {
            $this->setReplica(false);
        }

        return $this;
    }

    /**
     * Change the original receiver to a custom one.
     * @param int $chatId New receiver id.
     * @return BotMessageService
     */
    public function setReceiver($chatId): BotMessageService
    {
        $this->message->chat_id = $chatId;

        return $this;
    }

    /**
     * Set this message as replica for another message.
     * @param bool|null $enabled Controls replica status.
     * @return BotMessageService
     */
    public function setReplica(?bool $enabled = null): BotMessageService
    {
        $this->message->reply_to_message_id = $enabled !== false
            ? $this->updateMessage->message_id
            : null;

        return $this;
    }

    /**
     * Mark option as required.
     * @return BotMessageService
     */
    public function specificOptions(): BotMessageService
    {
        $this->optionsSpecifics = true;

        return $this;
    }

    /**
     * Unduplicate message by deleting the previous one.
     * This only happen on public chat.
     * @param string $identifier Unduplicate identifier.
     * @return BotMessageService
     */
    public function unduplicate(string $identifier): BotMessageService
    {
        $this->unduplicateIdentifier = $identifier;

        return $this;
    }
}
