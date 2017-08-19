<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Models\GridSubscription;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;

class UnsubscribeMoment extends SessionMoment
{
    use ModificationMoment;

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        if ($grid->isOwner($update->message->from)) {
            $botService = BotService::getInstance();
            $botService->sendOptionsMessage(
                $update->message->from->id,
                trans('GridModification.unsubscribeOwnerWizard'),
                PredefinitionService::getInstance()->optionsFrom(TransferOwnerMoment::getSubscribers($update, $process))
            );

            return;
        }

        $userSubscription = $grid->getUserSubscription($update->message->from);

        if ($userSubscription !== null) {
            $userSubscription->delete();
        }

        static::notifyUpdate($update, $process, trans('GridModification.unsubscribeYouUpdate'));
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

        $subscriptionOwner                    = $grid->getUserSubscription($update->message->from);
        $subscriptionOwner->subscription_rule = GridSubscription::RULE_USER;
        $subscriptionOwner->save();
        $subscriptionOwner->delete();

        $subscriberHim                    = $subscribers->where('gamertag_id', $input)->first();
        $subscriberHim->subscription_rule = GridSubscription::RULE_OWNER;
        $subscriberHim->save();

        static::notifyUpdate($update, $process, trans('GridModification.unsubscribeOwnerUpdate'));

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
            $botService->sendOptionsMessage(
                $update->message->from->id,
                trans('GridModification.errorUnsubscribeUserUnavailable'),
                PredefinitionService::getInstance()->optionsFrom($gridSubscribersGamertags)
            );

            return self::class;
        }

        return null;
    }
}
