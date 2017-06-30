<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Chat;
use Application\Adapters\Telegram\Update;
use Application\Services\SessionService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\SessionsProcessor\UserRegistrationSessionProcessor;
use Application\Strategies\Contracts\UpdateStrategyContract;

class EdgeCommandStrategy implements UpdateStrategyContract
{
    /**
     * @inheritdoc
     */
    public function process(Update $update): ?bool
    {
        $botService = BotService::getInstance();

        if ($update->message->text === '/start') {
            $botService->sendMessage(
                $update->message->chat->id,
                trans('UserHome.homeWelcomeBack', [
                    'homeCommands' => trans('UserHome.homeCommands'),
                ])
            );

            return true;
        }

        if ($update->message->text === '/comandos') {
            $botService->sendMessage(
                $update->message->chat->id,
                trans('UserHome.homeCommands')
            );

            return true;
        }

        if ($update->message &&
            $update->message->chat->type === Chat::TYPE_PRIVATE) {
            $botService->sendMessage(
                $update->message->chat->id,
                trans('UserHome.commandNotSupported', [
                    'homeCommands' => trans('UserHome.homeCommands'),
                ])
            );

            return true;
        }

        /** @var UserService $userService */
        $userService = app(UserService::class);
        $user        = $userService->get($update->message->from->id);

        if ($user === null) {
            SessionService::getInstance()->initializeProcessor(UserRegistrationSessionProcessor::class, $update);
        }

        return true;
    }
}
