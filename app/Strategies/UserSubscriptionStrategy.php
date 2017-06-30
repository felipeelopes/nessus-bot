<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\UserGamertag;
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

        if ($update->message->left_chat_member) {
            /** @var UserService $userService */
            $userService = app(UserService::class);
            $user        = $userService->get($update->message->left_chat_member->id);

            if ($update->message->left_chat_member->id !== $update->message->from->id) {
                $botService->sendMessage(
                    $update->message->chat->id,
                    trans('UserSubscription.userLeftAdmin', [
                        'admin' => $update->message->from->username
                            ? '@' . $update->message->from->username
                            : $update->message->from->getFullname(),
                    ])
                );
            }
            else if ($user === null) {
                $botService->sendMessage(
                    $update->message->chat->id,
                    trans('UserSubscription.userLeftUnknown')
                );
            }
            else {
                /** @var UserGamertag $userGamertags */
                $userGamertags = $user->gamertags->first();

                if ($userGamertags) {
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
