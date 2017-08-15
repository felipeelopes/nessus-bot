<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification\Traits;

use Application\Adapters\Grid as GridAdapter;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Models\GridSubscription;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
use Application\Services\UserService;
use Application\SessionsProcessor\GridModification\InitializationMoment;
use Application\Types\Process;
use Illuminate\Support\Collection;

trait ModificationMoment
{
    /**
     * Notify the a message with all update options.
     * @param Update      $update  Update instance.
     * @param Process     $process Process instance.
     * @param string|null $message Message.
     */
    public static function notifyMessage(Update $update, Process $process, string $message): void
    {
        $user = UserService::getInstance()->get($update->message->from->id);

        /** @var Grid $grid */
        $grid = $process->get(InitializationMoment::PROCESS_GRID);

        $availableOptions = (new Collection([
            [
                'value'       => InitializationMoment::REPLY_MODIFY_TITLE,
                'description' => trans('GridModification.modifyTitleOption'),
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_SUBTITLE,
                'description' => trans('GridModification.modifySubtitleOption'),
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_REQUIREMENTS,
                'description' => trans('GridModification.modifyRequirementsOption'),
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_TIMING,
                'description' => trans('GridModification.modifyTimingOption'),
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_DURATION,
                'description' => trans('GridModification.modifyDurationOption'),
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_PLAYERS,
                'description' => trans('GridModification.modifyPlayersOption'),
            ],
            [
                'value'       => InitializationMoment::REPLY_TRANSFER_OWNER,
                'description' => trans('GridModification.transferOwnerOption'),
                'conditional' => function () use ($user, $grid) {
                    /** @var GridSubscription $subscriberOwner */
                    $subscriberOwner = $grid->subscribers->where('subscription_rule', GridSubscription::RULE_OWNER)->first();

                    return $subscriberOwner->gamertag->id === $user->gamertag->id;
                },
            ],
        ]))->filter(function ($availableOption) {
            return !array_key_exists('conditional', $availableOption) ||
                   call_user_func($availableOption['conditional']) !== false;
        })->map(function ($availableOption) {
            return (new Collection($availableOption))
                ->except([ 'conditional' ])
                ->all();
        });

        $botService = BotService::getInstance();
        $botService->sendOptionsMessage(
            $update->message->chat->id,
            $message,
            PredefinitionService::getInstance()->optionsFrom($availableOptions)
        );
    }

    /**
     * Notify the update message with all update options.
     * @param Update  $update  Update instance.
     * @param Process $process Process instance.
     */
    public static function notifyOptions(Update $update, Process $process): void
    {
        self::notifyUpdate($update, $process, null);
    }

    /**
     * Notify the update message with all update options.
     * @param Update      $update      Update instance.
     * @param Process     $process     Process instance.
     * @param string|null $updateTitle Update title.
     */
    public static function notifyUpdate(Update $update, Process $process, ?string $updateTitle): void
    {
        /** @var Grid $grid */
        $grid = $process->get(InitializationMoment::PROCESS_GRID);

        $gridAdapter = GridAdapter::fromModel($grid);

        $updateMessage = $gridAdapter->getStructure(GridAdapter::STRUCTURE_TYPE_FULL);

        if ($updateTitle !== null) {
            $updateMessage = trans('GridModification.modificationUpdateNotify', [
                'title'     => $updateTitle,
                'structure' => $updateMessage,
            ]);
        }

        self::notifyMessage($update, $process, $updateMessage);
    }
}
