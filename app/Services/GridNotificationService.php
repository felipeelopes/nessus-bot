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
     * @throws \Exception
     */
    public function notifyMessage(Update $update, Grid $grid, string $message): void
    {
        $botService = BotService::getInstance();

        $isPrivate    = $update->message->isPrivate();
        $isFinished   = $grid->isCanceled() || $grid->isFinished();
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
                'conditional' => $isPrivate && !$isFinished && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_SUBTITLE,
                'description' => $administrativePrefix . trans('GridModification.modifySubtitleOption'),
                'conditional' => $isPrivate && !$isFinished && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_REQUIREMENTS,
                'description' => $administrativePrefix . trans('GridModification.modifyRequirementsOption'),
                'conditional' => $isPrivate && !$isFinished && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_TIMING,
                'description' => $administrativePrefix . trans('GridModification.modifyTimingOption'),
                'conditional' => $isPrivate && !$isFinished && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_DURATION,
                'description' => $administrativePrefix . trans('GridModification.modifyDurationOption'),
                'conditional' => $isPrivate && !$isFinished && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_PLAYERS,
                'description' => $administrativePrefix . trans('GridModification.modifyPlayersOption'),
                'conditional' => $isPrivate && !$isFinished && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_TRANSFER_OWNER,
                'description' => $administrativePrefix . trans('GridModification.transferOwnerOption'),
                'conditional' => $isPrivate && !$isFinished && $isOwner,
            ],
            [
                'value'       => InitializationMoment::REPLY_MODIFY_MANAGERS,
                'description' => $administrativePrefix . trans('GridModification.modifyManagersOption'),
                'conditional' => $isPrivate && !$isFinished && $isManager,
            ],
            [
                'value'       => InitializationMoment::REPLY_UNSUBSCRIBE,
                'description' => trans('GridModification.unsubscribeYouOption'),
                'conditional' => $isPrivate && !$isFinished && $isSubscriber && !$isOwner,
            ],
            [
                'value'       => InitializationMoment::REPLY_UNSUBSCRIBE,
                'description' => trans('GridModification.unsubscribeOwnerOption'),
                'conditional' => $isPrivate && !$isFinished && $isSubscriber && $isOwner,
            ],
            [
                'command'     => 'subscribeTitular',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isFinished && $grid->getVacancies() > 0,
            ],
            [
                'command'     => 'subscribeTitularReserve',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isFinished && $grid->getVacancies() === 0,
            ],
            [
                'command'     => 'subscribeReserve',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isFinished,
            ],
            [
                'command'     => 'subscribeObservation',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isFinished,
            ],
            [
                'command'     => 'gridManager',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isFinished,
            ],
            [
                'command'     => 'subscribeUnsubscribe',
                'arguments'   => [ 'id' => $grid->id ],
                'conditional' => !$isPrivate && !$isFinished,
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
     * @throws \Exception
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
            $update->message->forcePublic();

            $this->notifyMessage($update, $grid, $gridStructure);
        }
    }
}
