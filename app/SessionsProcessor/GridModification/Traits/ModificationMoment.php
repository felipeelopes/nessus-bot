<?php

declare(strict_types = 1);

namespace Application\SessionsProcessor\GridModification\Traits;

use Application\Adapters\Grid as GridAdapter;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\PredefinitionService;
use Application\Services\Telegram\BotService;
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
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        $isPrivate    = $update->message->isPrivate();
        $isCanceled   = $grid->isCanceled();
        $isOwner      = $grid->isOwner($update->message->from);
        $isManager    = $isOwner || $grid->isManager($update->message->from);
        $isSubscriber = $grid->isSubscriber($update->message->from);

        $isAdministratorOnly  = $update->message->from->isAdminstrator() && !$isSubscriber;
        $administrativePrefix = $isAdministratorOnly
            ? trans('GridModification.modifyAdministrative')
            : null;

        $availableOptions = (new Collection([
            [
                'value'       => InitializationMoment::REPLY_MODIFY_TITLE,
                'description' => $administrativePrefix . trans('GridModification.modifyTitleOption'),
                'conditional' => $isPrivate && !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_SUBTITLE,
                'description' => $administrativePrefix . trans('GridModification.modifySubtitleOption'),
                'conditional' => $isPrivate && !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_REQUIREMENTS,
                'description' => $administrativePrefix . trans('GridModification.modifyRequirementsOption'),
                'conditional' => $isPrivate && !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_TIMING,
                'description' => $administrativePrefix . trans('GridModification.modifyTimingOption'),
                'conditional' => $isPrivate && !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_DURATION,
                'description' => $administrativePrefix . trans('GridModification.modifyDurationOption'),
                'conditional' => $isPrivate && !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_PLAYERS,
                'description' => $administrativePrefix . trans('GridModification.modifyPlayersOption'),
                'conditional' => $isPrivate && !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_TRANSFER_OWNER,
                'description' => $administrativePrefix . trans('GridModification.transferOwnerOption'),
                'conditional' => $isPrivate && !$isCanceled && $isOwner,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_MANAGERS,
                'description' => $administrativePrefix . trans('GridModification.modifyManagersOption'),
                'conditional' => $isPrivate && !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_UNSUBSCRIBE,
                'description' => trans('GridModification.unsubscribeYouOption'),
                'conditional' => $isPrivate && !$isCanceled && $isSubscriber && !$isOwner,
            ],
            [
                'value'       => InitializationMoment::REPLY_UNSUBSCRIBE,
                'description' => trans('GridModification.unsubscribeOwnerOption'),
                'conditional' => $isPrivate && !$isCanceled && $isSubscriber && $isOwner,
            ],
            [
                'command'     => 'subscribeTitular',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isCanceled,
            ],
            [
                'command'     => 'subscribeTitularReserve',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isCanceled,
            ],
            [
                'command'     => 'subscribeReserve',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isCanceled,
            ],
            [
                'command'     => 'subscribeUnsubscribe',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isCanceled,
            ],
        ]))->filter(function ($availableOption) {
            return !array_key_exists('conditional', $availableOption) ||
                   $availableOption['conditional'] !== false;
        });

        $botService = BotService::getInstance();
        $botMessage = $botService->createMessage($update->message)
            ->setReplica(false)
            ->appendMessage($message)
            ->setOptions(PredefinitionService::getInstance()->optionsFrom($availableOptions), true)
            ->unduplicate('ModificationMoment@' . __FUNCTION__ . '@grid:' . $grid->id . '@chat:' . $update->message->chat->id);

        if ($isPrivate) {
            $botMessage->setCancelable();
        }

        $botMessage->publish();
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
     * @throws \Exception
     */
    public static function notifyUpdate(Update $update, Process $process, ?string $updateTitle): void
    {
        /** @var Grid $grid */
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

        $gridAdapter = GridAdapter::fromModel($grid);

        $gridStructure = $gridAdapter->getStructure(GridAdapter::STRUCTURE_TYPE_FULL);
        $updateMessage = $gridStructure;

        if ($updateTitle !== null) {
            $updateMessage = trans('GridModification.modificationUpdateNotify', [
                'title'     => $updateTitle,
                'structure' => $updateMessage,
            ]);
        }

        self::notifyMessage($update, $process, $updateMessage);

        if ($update->message->isPrivate()) {
            $publicUpdate          = clone $update;
            $publicUpdate->message = clone $publicUpdate->message;
            $publicUpdate->message->forcePublic();

            self::notifyMessage($publicUpdate, $process, $gridStructure);
        }
    }
}
