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

        $isCanceled   = $grid->isCanceled();
        $isOwner      = $grid->isOwner($update->message->from);
        $isManager    = $isOwner || $grid->isManager($update->message->from);
        $isSubscriber = $grid->isSubscriber($update->message->from);

        $availableOptions = (new Collection([
            [
                'value'       => InitializationMoment::REPLY_MODIFY_TITLE,
                'description' => trans('GridModification.modifyTitleOption'),
                'conditional' => !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_SUBTITLE,
                'description' => trans('GridModification.modifySubtitleOption'),
                'conditional' => !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_REQUIREMENTS,
                'description' => trans('GridModification.modifyRequirementsOption'),
                'conditional' => !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_TIMING,
                'description' => trans('GridModification.modifyTimingOption'),
                'conditional' => !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_DURATION,
                'description' => trans('GridModification.modifyDurationOption'),
                'conditional' => !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_PLAYERS,
                'description' => trans('GridModification.modifyPlayersOption'),
                'conditional' => !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_TRANSFER_OWNER,
                'description' => trans('GridModification.transferOwnerOption'),
                'conditional' => !$isCanceled && $isOwner,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_MANAGERS,
                'description' => trans('GridModification.modifyManagersOption'),
                'conditional' => !$isCanceled && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_UNSUBSCRIBE,
                'description' => trans('GridModification.unsubscribeYouOption'),
                'conditional' => !$isCanceled && $isSubscriber && !$isOwner,
            ],
            [
                'value'       => InitializationMoment::REPLY_UNSUBSCRIBE,
                'description' => trans('GridModification.unsubscribeOwnerOption'),
                'conditional' => !$isCanceled && $isSubscriber && $isOwner,
            ],
        ]))->filter(function ($availableOption) {
            return !array_key_exists('conditional', $availableOption) ||
                   $availableOption['conditional'] !== false;
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
        $grid = (new Grid)->find($process->get(InitializationMoment::PROCESS_GRID_ID));

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
