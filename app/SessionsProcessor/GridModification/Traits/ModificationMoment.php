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

trait ModificationMoment
{
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

        $botService = BotService::getInstance();
        $botService->sendPredefinedMessage(
            $update->message->chat->id,
            $updateMessage,
            PredefinitionService::getInstance()->optionsFrom(trans('GridModification.modificationOptions'))
        );
    }
}
