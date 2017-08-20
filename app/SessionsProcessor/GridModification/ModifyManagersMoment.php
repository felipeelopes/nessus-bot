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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ModifyManagersMoment extends SessionMoment
{
    use ModificationMoment;

    /**
     * Returns the subscribers from grid except you.
     * @param Process $process Process instance.
     * @return GridSubscription[]|Collection
     */
    private static function getSubscribers(Process $process): Collection
    {
        /** @var Grid|Builder $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));
        $grid->load([
            'subscribers' => function (HasMany $builder) {
                /** @var GridSubscription $builder */
                $builder->orderByGridRule();
                $builder->orderByGamertag();
            },
            'subscribers.gamertag',
        ]);

        return $grid->subscribers;
    }

    /**
     * Returns the subscribers from grid except you.
     * @param Update  $update  Update instance.
     * @param Process $process Process instance.
     * @return array
     */
    private static function getSubscribersParsed(Update $update, Process $process): array
    {
        $user = UserService::getInstance()->get($update->message->from->id);

        $gridSubscribersGamertags = [];

        foreach (self::getSubscribers($process) as $subscriber) {
            $subscriberGamertag = $subscriber->gamertag;

            if ($subscriberGamertag->id === $user->gamertag->id &&
                !$update->message->from->isAdminstrator()) {
                continue;
            }

            if ($subscriber->isOwner()) {
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
        $user = UserService::getInstance()->get($update->message->from->id);

        $gridSubscribersGamertags = [];

        foreach (self::getSubscribers($process) as $subscriber) {
            $subscriberGamertag = $subscriber->gamertag;

            if ($subscriberGamertag->id === $user->gamertag->id &&
                !$update->message->from->isAdminstrator()) {
                continue;
            }

            if ($subscriber->isOwner()) {
                continue;
            }

            $gamertagValue = $subscriber->isManager()
                ? trans('GridModification.modifyManagerRevoke', [ 'gamertag' => $subscriber->gamertag->gamertag_value ])
                : trans('GridModification.modifyManagerAdd', [ 'gamertag' => $subscriber->gamertag->gamertag_value ]);

            $gridSubscribersGamertags[] = [
                'value'       => $subscriberGamertag->id,
                'description' => $gamertagValue,
            ];
        }

        $botService = BotService::getInstance();
        $botService->createMessage($update->message)
            ->setCancelable()
            ->appendMessage(trans('GridModification.modifyManagersWizard'))
            ->setOptions(PredefinitionService::getInstance()->optionsFrom($gridSubscribersGamertags), true)
            ->publish();
    }

    /**
     * @inheritdoc
     */
    public function save(?string $input, Update $update, Process $process): ?string
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        /** @var GridSubscription $subscriberModified */
        $subscribers        = $grid->subscribers;
        $subscriberModified = $subscribers->where('gamertag_id', $input)->first();

        $subscriberGamertag = [ 'value' => $subscriberModified->gamertag->gamertag_value ];

        if ($subscriberModified->subscription_rule === GridSubscription::RULE_MANAGER) {
            $subscriberModified->subscription_rule = GridSubscription::RULE_USER;
            $subscriberModified->save();

            static::notifyUpdate($update, $process, trans('GridModification.modifyManagerRevokeUpdate', $subscriberGamertag));
        }
        else if ($subscriberModified->subscription_rule === GridSubscription::RULE_USER) {
            $subscriberModified->subscription_rule = GridSubscription::RULE_MANAGER;
            $subscriberModified->save();

            static::notifyUpdate($update, $process, trans('GridModification.modifyManagerAddUpdate', $subscriberGamertag));
        }

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
            self::notifyMessage($update, $process, trans('GridModification.modifyManagersIsEmpty'));

            throw new ForceMomentException(InitializationMoment::class);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function validateInput(?string $input, Update $update, Process $process): ?string
    {
        $gridSubscribersGamertags    = self::getSubscribersParsed($update, $process);
        $gridSubscribersGamertagsIds = array_pluck($gridSubscribersGamertags, 'value');

        if (!in_array($input, $gridSubscribersGamertagsIds, false)) {
            $botService = BotService::getInstance();
            $botService->createMessage($update->message)
                ->setCancelable()
                ->appendMessage(trans('GridModification.errorModifyManagerUnavailable'))
                ->setOptions(PredefinitionService::getInstance()->optionsFrom($gridSubscribersGamertags), true)
                ->publish();

            return self::class;
        }

        return null;
    }
}
