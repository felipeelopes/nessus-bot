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
                $botService->sendMessage(
                    $update->message->chat->id,
                    trans('UserRegistration.toPrivate', [
                        'fullname'    => $update->message->new_chat_member->getFullname(),
                        'botUsername' => '@' . $botService->getMe()->username,
                    ])
                );
            }
            else {
                $userGamertags = $user->gamertag;

                if ($userGamertags) {
                    $botService->sendMessage(
                        $update->message->chat->id,
                        trans('UserRegistration.welcomeAgain', [
                            'fullname' => $update->message->new_chat_member->getFullname(),
                            'gamertag' => $userGamertags->gamertag_value,
                        ])
                    );
                }
            }
        }

        if ($update->message->left_chat_member) {
            /** @var UserService $userService */
            $userService = MockupService::getInstance()->instance(UserService::class);
            $user        = $userService->get($update->message->left_chat_member->id);

            if ($update->message->left_chat_member->id !== $update->message->from->id) {
                $userGamertags = $user->gamertag;

                $botService->sendSticker($update->message->chat->id, 'CAADAQADBwADwvySEXi2rT98M7GIAg');
                $botService->sendMessage(
                    $update->message->chat->id,
                    trans('UserSubscription.userLeftAdmin', [
                        'admin'    => $update->message->from->username
                            ? '@' . $update->message->from->username
                            : $update->message->from->getFullname(),
                        'fullname' => $update->message->left_chat_member->getFullname(),
                        'gamertag' => $userGamertags->gamertag_value,
                    ])
                );
            }
            else if ($user === null) {
                $botService->sendSticker($update->message->chat->id, 'CAADAQADBgADwvySEejmQn82duSBAg');
                $botService->sendMessage(
                    $update->message->chat->id,
                    trans('UserSubscription.userLeftUnknown', [
                        'fullname' => $update->message->left_chat_member->getFullname(),
                    ])
                );
            }
            else {
                $userGamertags = $user->gamertag;

                if ($userGamertags) {
                    $botService->sendSticker($update->message->chat->id, 'CAADAQADBgADwvySEejmQn82duSBAg');
                    $botService->sendMessage(
                        $update->message->chat->id,
                        trans('UserSubscription.userLeftKnown', [
                            'fullname' => $update->message->left_chat_member->getFullname(),
                            'gamertag' => $userGamertags->gamertag_value,
                        ])
                    );

                    $bot = $botService->getMe();
                    $botService->sendMessage(
                        $update->message->left_chat_member->id,
                        trans('UserSubscription.thankfulMessage', [ 'botname' => $bot->getFullname() ])
                    );
                }
            }

            return true;
        }

        return null;
    }
}
