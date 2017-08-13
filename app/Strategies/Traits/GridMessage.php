<?php

declare(strict_types = 1);

namespace Application\Strategies\Traits;

use Application\Adapters\Predefinition\OptionItem;
use Application\Adapters\Telegram\Message;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\CommandService;
use Application\Services\FormattingService;
use Application\Services\Requester\RequesterService;
use Application\Services\Telegram\BotService;
use Application\Strategies\GridListingStrategy;
use Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait GridMessage
{
    /**
     * Delete previous listing message.
     * @param string     $cacheKey   Cache key to delete.
     * @param BotService $botService Bot Service instance.
     */
    private function deletePrevious(string $cacheKey, BotService $botService): void
    {
        /** @var Message $messageLast */
        $messageLast = Cache::get($cacheKey);

        if ($messageLast) {
            $botService->deleteMessage($messageLast->chat->id, $messageLast->message_id);

            Cache::forget($cacheKey);
        }
    }

    /**
     * Returns the grid status, based on some conditions.
     * @param Grid $grid Grid instance.
     * @return string|null
     */
    private function getGridStatusText(Grid $grid): ?string
    {
        if ($grid->grid_status) {
            return trans('Grid.status' . Str::ucfirst($grid->grid_status));
        }

        return null;
    }

    /**
     * Returns the grid title, based on some conditions.
     * @param Grid $grid Grid instance.
     * @return string
     */
    private function getGridTitle(Grid $grid): string
    {
        if ($grid->isPlaying()) {
            return trans('GridListing.titlePlaying');
        }

        if ($grid->isToday()) {
            return trans('GridListing.titleToday');
        }

        return trans('GridListing.titleTomorrow');
    }

    /**
     * Send grid listing to chat.
     * @param Update            $update     Update instace.
     * @param BotService        $botService Bot Service instance.
     * @param Collection|Grid[] $grids      Grids.
     * @param string            $mode       Grid mode (MODE consts).
     * @return bool|null
     */
    private function sendGridListing(Update $update, BotService $botService, Collection $grids, string $mode): ?bool
    {
        $cacheListKey = __CLASS__ . '@listing';

        if ($grids->isEmpty()) {
            $botService->sendPredefinedMessage(
                $update->message->chat->id,
                trans('GridListing.isEmpty'),
                [ OptionItem::fromCommand(CommandService::COMMAND_NEW_GRID) ],
                false
            );

            if (!$update->message->isPrivate()) {
                $this->deletePrevious($cacheListKey, $botService);
            }

            return true;
        }

        $currentTitle = null;
        $result       = null;

        /** @var Grid $grid */
        foreach ($grids as $grid) {
            $gridTitle = $mode === GridListingStrategy::MODE_GENERAL
                ? $this->getGridTitle($grid)
                : trans('GridListing.titleBase', [
                    'title' => Str::ucfirst($this->getGridStatusText($grid)),
                ]);

            if ($currentTitle !== $gridTitle) {
                if ($currentTitle !== null) {
                    $result .= "\n";
                }

                $result       .= $gridTitle;
                $currentTitle = $gridTitle;
            }

            $gridSubtitle = null;

            if ($grid->grid_subtitle) {
                $gridSubtitle = trans('GridListing.itemSubtitle', [
                    'subtitle' => $grid->getShortSubtitle(),
                ]);
            }

            $result .= trans('GridListing.item', [
                'timing'     => $grid->grid_timing->format('H:i'),
                'command'    => '/' . trans('Command.commands.' . $mode . 'ShortCommand') . $grid->id,
                'players'    => $grid->countPlayers(),
                'maxPlayers' => $grid->grid_players,
                'reserves'   => FormattingService::toSuperscript((string) $grid->countReserves()),
                'title'      => FormattingService::ellipsis($grid->grid_title, 20),
                'subtitle'   => $gridSubtitle,
            ]);
        }

        $message = $botService->sendPredefinedMessage(
            $update->message->chat->id,
            $result,
            [ OptionItem::fromCommand(CommandService::COMMAND_NEW_GRID) ],
            false
        );

        if (!$update->message->isPrivate()) {
            $this->deletePrevious($cacheListKey, $botService);
            Cache::put($cacheListKey, $message, RequesterService::CACHE_DAY);
        }

        return null;
    }
}
