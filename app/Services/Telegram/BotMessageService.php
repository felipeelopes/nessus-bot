<?php

declare(strict_types = 1);

namespace Application\Services\Telegram;

use Application\Adapters\Predefinition\OptionItem;
use Application\Adapters\Telegram\Message;
use Application\Adapters\Telegram\SendMessage;
use Application\Services\CommandService;
use Application\Services\PredefinitionService;

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

        $this->setReceiver($updateMessage->chat->id);
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

        return BotService::getInstance()->publishMessage($this->message);
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
     * Mark option as required.
     * @return BotMessageService
     */
    public function specificOptions(): BotMessageService
    {
        $this->optionsSpecifics = true;

        return $this;
    }
}
