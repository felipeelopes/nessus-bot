<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\User;
use Application\Services\MockupService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\SessionsProcessor\UserRegistration\WelcomeMoment;
use Application\Strategies\Contracts\UpdateStrategyContract;
use Illuminate\Database\Eloquent\Builder;

class UserSubscriptionStrategy implements UpdateStrategyContract
{
    /**
     * @inheritdoc
     */
    public function process(Update $update): ?bool
    {
        $botService = BotService::getInstance();

        if ($update->message->new_chat_member) {
            if ($update->message->new_chat_member->is_bot) {
                return true;
            }

            /** @var User|Builder $userQuery */
            $userQuery = User::query();
            $userQuery->whereUserNumber($update->message->new_chat_member->id);
            $userQuery->withTrashed();
            $user = $userQuery->first();

            if ($user === null) {
                $botService->createMessage($update->message)
                    ->appendMessage(trans('UserRegistration.toPrivate', [
                        'mention' => $update->message->new_chat_member->getMention(),
                    ]))
                    ->unduplicate(WelcomeMoment::class . '@' . __FUNCTION__ . '@' . $update->message->new_chat_member->id)
                    ->addLinkButton(
                        trans('UserRegistration.toPrivateButton'),
                        trans('UserRegistration.toPrivateLink', [
                            'botname' => $botService->getMe()->username,
                        ])
                    )
                    ->setReplica(false)
                    ->publish();
            }
            else {
                $user->restore();

                $userGamertags = $user->gamertag;

                if ($userGamertags) {
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('UserRegistration.welcomeAgain', [
                            'mention'  => $update->message->new_chat_member->getMention(),
                            'gamertag' => $userGamertags->gamertag_value,
                        ]))
                        ->setReplica(false)
                        ->publish();
                }
            }
        }

        if ($update->message->left_chat_member) {
            if ($update->message->left_chat_member->is_bot) {
                return true;
            }

            /** @var UserService $userService */
            $userService = MockupService::getInstance()->instance(UserService::class);
            $user        = $userService->get($update->message->left_chat_member->id);

            if ($user === null) {
                if ($update->message->left_chat_member->id !== $update->message->from->id) {
                    $botService->sendSticker($update->message->chat->id, 'CAADAQADBwADwvySEXi2rT98M7GIAg');
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('UserSubscription.userLeftUnknowAdmin', [
                            'admin'    => $update->message->from->getMention(),
                            'fullname' => $update->message->left_chat_member->getFullname(),
                        ]))
                        ->setReplica(false)
                        ->publish();

                    return true;
                }

                $botService->sendSticker($update->message->chat->id, 'CAADAQADBgADwvySEejmQn82duSBAg');
                $botService->createMessage($update->message)
                    ->appendMessage(trans('UserSubscription.userLeftUnknown', [
                        'fullname' => $update->message->left_chat_member->getFullname(),
                    ]))
                    ->setReplica(false)
                    ->publish();

                return true;
            }

            if ($update->message->left_chat_member->id !== $update->message->from->id) {
                $userGamertags = $user->gamertag;

                $botService->sendSticker($update->message->chat->id, 'CAADAQADBwADwvySEXi2rT98M7GIAg');
                $botService->createMessage($update->message)
                    ->appendMessage(trans('UserSubscription.userLeftAdmin', [
                        'admin'    => $update->message->from->getMention(),
                        'fullname' => $update->message->left_chat_member->getFullname(),
                        'gamertag' => $userGamertags->gamertag_value,
                    ]))
                    ->setReplica(false)
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
                        ->setReplica(false)
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

            $user->delete();

            return true;
        }

        return null;
    }
}
