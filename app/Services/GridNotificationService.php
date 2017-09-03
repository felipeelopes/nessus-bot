<?php

declare(strict_types = 1);

namespace Application\Services;

use Application\Adapters\Grid as GridAdapter;
use Application\Adapters\Telegram\Update;
use Application\Events\GridRespawnExecutor;
use Application\Models\Grid;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\GridModification\InitializationMoment;
use Illuminate\Support\Collection;

class GridNotificationService
{
    /**
     * @inheritdoc
     */
    public static function getInstance(): GridNotificationService
    {
        return MockupService::getInstance()->instance(static::class);
    }

    /**
     * Notify the a message with all update options.
     * @param Update      $update  Update instance.
     * @param Grid        $grid    Grid instance.
     * @param string|null $message Message.
     */
    public function notifyMessage(Update $update, Grid $grid, string $message): void
    {
        $botService = BotService::getInstance();

        $isPrivate    = $update->message->isPrivate();
        $isCanceled   = $grid->isCanceled();
        $isOwner      = $grid->isOwner($update->message->from);
        $isManager    = $isOwner || $grid->isManager($update->message->from);
        $isSubscriber = $grid->isSubscriber($update->message->from);

        $isAdministratorOnly  = $update->message->from->isAdminstrator() &&
                                (!$isSubscriber || $grid->isUser($update->message->from, true));
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
                'conditional' => !$isPrivate && !$isCanceled && $grid->getVacancies() > 0,
            ],
            [
                'command'     => 'subscribeTitularReserve',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isCanceled && $grid->getVacancies() === 0,
            ],
            [
                'command'     => 'subscribeReserve',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isCanceled,
            ],
            [
                'command'     => 'subscribeObservation',
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

        $botMessage = $botService->createMessage($update->message)
            ->setReplica(false)
            ->appendMessage($message)
            ->setOptions(PredefinitionService::getInstance()->optionsFrom($availableOptions), true)
            ->unduplicate('ModificationMoment@' . __FUNCTION__ . '@grid:' . $grid->id . '@chat:' . $update->message->chat->id);

        if ($isPrivate) {
            $botMessage->setCancelable();
        }

        $botMessage->publish();

        $respawnReference = SettingService::fromReference($grid, GridRespawnExecutor::LAST_RESPAWN_REFERENCE);
        $respawnReference->touch();
    }

    /**
     * Notify the update message with all update options.
     * @param Update      $update      Update instance.
     * @param Grid        $grid        Grid instance.
     * @param string|null $updateTitle Update title.
     */
    public function notifyUpdate(Update $update, Grid $grid, ?string $updateTitle = null): void
    {
        $gridAdapter = GridAdapter::fromModel($grid);

        $gridStructure = $gridAdapter->getStructure(GridAdapter::STRUCTURE_TYPE_FULL);
        $updateMessage = $gridStructure;

        if ($updateTitle !== null) {
            $updateMessage = trans('GridModification.modificationUpdateNotify', [
                'title'     => $updateTitle,
                'structure' => $updateMessage,
            ]);
        }

        $this->notifyMessage($update, $grid, $updateMessage);

        if ($updateTitle !== null && $update->message->isPrivate()) {
            $updateCopy          = clone $update;
            $updateCopy->message = clone $updateCopy->message;
            $updateCopy->message->forcePublic();

            $this->notifyMessage($updateCopy, $grid, $gridStructure);
        }
    }
}
