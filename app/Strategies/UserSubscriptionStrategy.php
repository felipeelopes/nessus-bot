<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Services\MockupService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\Strategies\Contracts\UpdateStrategyContract;

class UserSubscriptionStrategy implements UpdateStrategyContract
{
    /**
     * @inheritdoc
     */
    public function process(Update $update): ?bool
    {
        $botService = BotService::getInstance();

        if ($update->message->new_chat_member) {
            /** @var UserService $userService */
            $userService = MockupService::getInstance()->instance(UserService::class);
            $user        = $userService->get($update->message->new_chat_member->id);

            if ($user === null) {
                $botService->createMessage($update->message)
                    ->appendMessage(trans('UserRegistration.toPrivate', [
                        'fullname' => $update->message->new_chat_member->getFullname(),
                    ]))
                    ->publish();
            }
            else {
                $userGamertags = $user->gamertag;

                if ($userGamertags) {
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('UserRegistration.welcomeAgain', [
                            'fullname' => $update->message->new_chat_member->getFullname(),
                            'gamertag' => $userGamertags->gamertag_value,
                        ]))
                        ->publish();
                }
            }
        }

        if ($update->message->left_chat_member) {
            /** @var UserService $userService */
            $userService = MockupService::getInstance()->instance(UserService::class);
            $user        = $userService->get($update->message->left_chat_member->id);

            if ($user === null) {
                return true;
            }

            if ($update->message->left_chat_member->id !== $update->message->from->id) {
                $userGamertags = $user->gamertag;

                $botService->sendSticker($update->message->chat->id, 'CAADAQADBwADwvySEXi2rT98M7GIAg');
                $botService->createMessage($update->message)
                    ->appendMessage(trans('UserSubscription.userLeftAdmin', [
                        'admin'    => $update->message->from->username
                            ? '@' . $update->message->from->username
                            : $update->message->from->getFullname(),
                        'fullname' => $update->message->left_chat_member->getFullname(),
                        'gamertag' => $userGamertags->gamertag_value,
                    ]))
                    ->publish();
            }
            else if ($user === null) {
                $botService->sendSticker($update->message->chat->id, 'CAADAQADBgADwvySEejmQn82duSBAg');
                $botService->createMessage($update->message)
                    ->appendMessage(trans('UserSubscription.userLeftUnknown', [
                        'fullname' => $update->message->left_chat_member->getFullname(),
                    ]))
                    ->publish();
            }
            else {
                $userGamertags = $user->gamertag;

                if ($userGamertags) {
                    $botService->sendSticker($update->message->chat->id, 'CAADAQADBgADwvySEejmQn82duSBAg');
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('UserSubscription.userLeftKnown', [
                            'fullname' => $update->message->left_chat_member->getFullname(),
                            'gamertag' => $userGamertags->gamertag_value,
                        ]))
                        ->publish();

                    $bot = $botService->getMe();
                    $botService->createMessage($update->message)
                        ->setReceiver($update->message->left_chat_member->id)
                        ->appendMessage(trans('UserSubscription.thankfulMessage', [
                            'botname' => $bot->getFullname(),
                        ]))
                        ->publish();
                }
            }

            return true;
        }

        return null;
    }
}
