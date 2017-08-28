<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Exceptions\SessionProcessor\ForceMomentException;
use Application\Models\Grid;
use Application\Models\GridSubscription;
use Application\Services\GridNotificationService;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\Types\Process;

class UnsubscribeMoment extends SessionMoment
{
    public const CANCEL_ACCESS_ISSUE    = 'accessIssue';
    public const CANCEL_LACK_INTEREST   = 'lackInterest';
    public const CANCEL_LACK_PLAYERS    = 'lackPlayers';
    public const CANCEL_OTHERS          = 'others';
    public const CANCEL_PERSONAL_REASON = 'personalReason';

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        if ($grid->subscribers->count() === 1) {
            $predefinedReasons = [
                [ 'value' => self::CANCEL_PERSONAL_REASON, 'description' => trans('GridModification.unsubscribeCancelPersonalReason') ],
                [ 'value' => self::CANCEL_LACK_PLAYERS, 'description' => trans('GridModification.unsubscribeCancelLackPlayers') ],
                [ 'value' => self::CANCEL_LACK_INTEREST, 'description' => trans('GridModification.unsubscribeCancelLackInterest') ],
                [ 'value' => self::CANCEL_ACCESS_ISSUE, 'description' => trans('GridModification.unsubscribeCancelAccessIssue') ],
                [ 'value' => self::CANCEL_OTHERS, 'description' => trans('GridModification.unsubscribeCancelOthers') ],
            ];

            $botService = BotService::getInstance();
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridModification.unsubscribeCancelWizard'))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom($predefinedReasons))
                ->publish();

            throw new ForceMomentException(UnsubscribeCancelMoment::class);
        }

        if ($grid->isOwner($update->message->from)) {
            $botService = BotService::getInstance();
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridModification.unsubscribeOwnerWizard'))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom(TransferOwnerMoment::getSubscribers($update, $process)), true)
                ->publish();

            return;
        }

        $userSubscription = $grid->getUserSubscription($update->message->from);

        if ($userSubscription !== null) {
            $userSubscription->delete();
        }
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        /** @var GridSubscription $subscriberHim */
        $subscribers = $grid->subscribers;

        $subscriberHim = $subscribers->where('gamertag_id', $input)->first();

        if (!$subscriberHim) {
            $botService = BotService::getInstance();
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridModification.errorUnsubscribeUserUnavailable'))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom(TransferOwnerMoment::getSubscribers($update, $process)), true)
                ->publish();

            return self::class;
        }

        $subscriberHim->subscription_rule = GridSubscription::RULE_OWNER;
        $subscriberHim->save();

        $subscriptionOwner                    = $grid->getUserSubscription($update->message->from);
        $subscriptionOwner->subscription_rule = GridSubscription::RULE_USER;
        $subscriptionOwner->delete();

        GridNotificationService::getInstance()
            ->notifyUpdate($update, $grid, trans('GridModification.unsubscribeOwnerUpdate'));

        return InitializationMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        $gridSubscribersGamertags   = TransferOwnerMoment::getSubscribers($update, $process);
        $gridSubscribersGamertagIds = array_pluck($gridSubscribersGamertags, 'value');

        if (!in_array($input, $gridSubscribersGamertagIds, false)) {
            $botService = BotService::getInstance();
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridModification.errorUnsubscribeUserUnavailable'))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom($gridSubscribersGamertags), true)
                ->publish();

            return self::class;
        }

        return null;
    }
}
