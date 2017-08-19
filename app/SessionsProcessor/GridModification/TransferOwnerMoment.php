<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification;

use Application\Adapters\Telegram\Update;
use Application\Exceptions\SessionProcessor\ForceMomentException;
use Application\Models\Grid;
use Application\Models\GridSubscription;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\SessionsProcessor\Definition\SessionMoment;
use Application\SessionsProcessor\GridModification\Traits\ModificationMoment;
use Application\Types\Process;
use Illuminate\Database\Eloquent\Builder;

class TransferOwnerMoment extends SessionMoment
{
    use ModificationMoment;

    /**
     * Returns the subscribers from grid except you.
     * @param Update  $update  Update instance.
     * @param Process $process Process instance.
     * @return array
     */
    public static function getSubscribers(Update $update, Process $process): array
    {
        $user = UserService::getInstance()->get($update->message->from->id);

        /** @var Grid|Builder $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));
        $grid->load('subscribers.gamertag');

        $gridSubscribersGamertags = [];

        foreach ($grid->subscribers as $subscriber) {
            $subscriberGamertag = $subscriber->gamertag;

            if ($subscriberGamertag->id === $user->gamertag->id) {
                continue;
            }

            $gridSubscribersGamertags[] = [
                'value'       => $subscriberGamertag->id,
                'description' => $subscriber->gamertag->gamertag_value,
            ];
        }

        return $gridSubscribersGamertags;
    }

    /**
     * @inheritdoc
     */
    public function request(Update $update, Process $process): void
    {
        $gridSubscribersGamertags = self::getSubscribers($update, $process);

        $botService = BotService::getInstance();
        $botService->createMessage($update->message)
            ->setCancelable()
            ->appendMessage(trans('GridModification.transferOwnerWizard'))
            ->setOptions(PredefinitionService::getInstance()->optionsFrom($gridSubscribersGamertags), true)
            ->publish();
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        $user = UserService::getInstance()->get($update->message->from->id);

        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        /** @var GridSubscription $subscriberHim */
        $subscribers = $grid->subscribers;

        /** @var GridSubscription $subscriberYou */
        $subscriberYou                    = $subscribers->where('gamertag_id', $user->gamertag->id)->first();
        $subscriberYou->subscription_rule = GridSubscription::RULE_MANAGER;
        $subscriberYou->save();

        $subscriberHim                    = $subscribers->where('gamertag_id', $input)->first();
        $subscriberHim->subscription_rule = GridSubscription::RULE_OWNER;
        $subscriberHim->save();

        static::notifyUpdate($update, $process, trans('GridModification.transferOwnerUpdated', [
            'value' => $subscriberHim->gamertag->gamertag_value,
        ]));

        return InitializationMoment::class;
    }

    /**
     * @inheritdoc
     */
    public function validateInitialization(Update $update, Process $process): bool
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        if ($grid->subscribers->count() < 2) {
            self::notifyMessage($update, $process, trans('GridModification.transferOwnerIsEmpty'));

            throw new ForceMomentException(InitializationMoment::class);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        $gridSubscribersGamertags   = self::getSubscribers($update, $process);
        $gridSubscribersGamertagIds = array_pluck($gridSubscribersGamertags, 'value');

        if (!in_array($input, $gridSubscribersGamertagIds, false)) {
            $botService = BotService::getInstance();
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridModification.errorTransferOwnerUnavailable'))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom($gridSubscribersGamertags), true)
                ->publish();

            return self::class;
        }

        return null;
    }
}
