<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\User;
use Application\Models\UserGamertag;
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
            /** @var UserService $userService */
            $userService = MockupService::getInstance()->instance(UserService::class);
            $user        = $userService->get($update->message->new_chat_member->id);

            /** @var User|Builder|mixed $userQuery */
            $userQuery = User::query();
            $userQuery->whereUserNumber($update->message->new_chat_member->id);
            $userQuery->withTrashed();
            $user = $userQuery->first();

            if ($user === null) {
                $botService->createMessage($update->message)
                    ->appendMessage(trans('UserRegistration.toPrivate', [
                        'fullname' => $update->message->new_chat_member->getFullname(),
                    ]))
                    ->unduplicate(WelcomeMoment::class . '@' . __FUNCTION__ . '@' . $update->message->new_chat_member->id)
                    ->addLinkButton(
                        trans('UserRegistration.toPrivateButton'),
                        trans('UserRegistration.toPrivateLink', [
                            'botname' => $botService->getMe()->username,
                        ])
                    )
                    ->publish();
            }
            else {
                $user->restore();

                /** @var UserGamertag|mixed $userGamertagRelation */
                $userGamertagRelation = $user->gamertag();
                $userGamertagRelation->withTrashed()
                    ->first()
                    ->restore();

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
                if ($update->message->left_chat_member->id !== $update->message->from->id) {
                    $botService->sendSticker($update->message->chat->id, 'CAADAQADBwADwvySEXi2rT98M7GIAg');
                    $botService->createMessage($update->message)
                        ->appendMessage(trans('UserSubscription.userLeftUnknowAdmin', [
                            'admin'    => $update->message->from->getMention(),
                            'fullname' => $update->message->left_chat_member->getFullname(),
                        ]))
                        ->publish();

                    return true;
                }

                $botService->sendSticker($update->message->chat->id, 'CAADAQADBgADwvySEejmQn82duSBAg');
                $botService->createMessage($update->message)
                    ->appendMessage(trans('UserSubscription.userLeftUnknown', [
                        'fullname' => $update->message->left_chat_member->getFullname(),
                    ]))
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

            $user->gamertag->delete();
            $user->delete();

            return true;
        }

        return null;
    }
}
