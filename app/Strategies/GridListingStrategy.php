<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Grid as GridAdapter;
use Application\Adapters\Predefinition\OptionItem;
use Application\Adapters\Telegram\Message;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Models\User;
use Application\Services\CommandService;
use Application\Services\FormattingService;
use Application\Services\Requester\RequesterService;
use Application\Services\Telegram\BotService;
use Application\Strategies\Contracts\UserStrategyContract;
use Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GridListingStrategy implements UserStrategyContract
{
    private const CACHE_GRID_KEY    = __CLASS__ . '@grid:';
    private const CACHE_LISTING_KEY = __CLASS__ . '@listing';

    private const MODE_GENERAL = 'gridShow';
    private const MODE_OWNEDS  = 'myGridsShow';

    /**
     * @inheritdoc
     */
    public function process(?User $user, Update $update): ?bool
    {
        if ($user === null) {
            return null;
        }

        if ($update->message->isCommand(CommandService::COMMAND_LIST_GRIDS)) {
            $botService = BotService::getInstance();

            /** @var Grid|Builder $gridsQuery */
            $gridsQuery = Grid::query();
            $gridsQuery->with('subscribers');
            $gridsQuery->filterOpeneds();
            $grids = $gridsQuery->get()->sort(function (Grid $gridA, Grid $gridB) {
                return $gridB->isPlaying() <=> $gridA->isPlaying()
                    ?: $gridB->isToday() <=> $gridA->isToday()
                        ?: $gridB->grid_timing->lt($gridA->grid_timing);
            });

            $this->sendGridListing($update, $botService, $grids, self::MODE_GENERAL);

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_MY_GRIDS)) {
            $botService = BotService::getInstance();

            /** @var Grid|Builder $gridsQuery */
            $gridsQuery = Grid::query();
            $gridsQuery->with('subscribers');
            $gridsQuery->filterOwneds($user);
            $grids = $gridsQuery->get()->sort(function (Grid $gridA, Grid $gridB) {
                return ($gridB->grid_status === Grid::STATUS_CANCELED) <=> ($gridA->grid_status === Grid::STATUS_CANCELED)
                    ?: ($gridB->grid_status === Grid::STATUS_FINISHED) <=> ($gridA->grid_status === Grid::STATUS_FINISHED)
                        ?: $gridB->grid_timing->lt($gridA->grid_timing);
            });

            $this->sendGridListing($update, $botService, $grids, self::MODE_OWNEDS);

            return true;
        }

        if ($update->message->isCommand(CommandService::COMMAND_GRID_SHOW_SHORT)) {
            $commandArguments = $update->message->getCommand()->arguments;

            if (count($commandArguments) < 1) {
                return null;
            }

            $botService = BotService::getInstance();

            /** @var Builder $gridQuery */
            /** @var Grid $grid */
            $gridQuery = Grid::query();
            $gridQuery->with('subscribers.gamertag');
            $grid = $gridQuery->find($commandArguments[0]);

            if (!$grid) {
                $botService->sendMessage($update->message->chat->id, trans('GridListing.errorGridNotFound'));

                return true;
            }

            $gridAdapter = GridAdapter::fromModel($grid);

            $gridMessage = $botService->sendMessage(
                $update->message->chat->id,
                $gridAdapter->getStructure(GridAdapter::STRUCTURE_TYPE_FULL)
            );

            if (!$update->message->isPrivate()) {
                $gridCacheKey = self::CACHE_GRID_KEY . $grid->id;

                $this->deletePrevious($gridCacheKey, $botService);
                Cache::put($gridCacheKey, $gridMessage, RequesterService::CACHE_DAY);
            }

            return true;
        }

        return null;
    }

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
        if ($grids->isEmpty()) {
            $botService->sendPredefinedMessage(
                $update->message->chat->id,
                trans('GridListing.isEmpty'),
                [ OptionItem::fromCommand(CommandService::COMMAND_NEW_GRID) ],
                false
            );

            if (!$update->message->isPrivate()) {
                $this->deletePrevious(self::CACHE_LISTING_KEY, $botService);
            }

            return true;
        }

        $currentTitle = null;
        $result       = null;

        /** @var Grid $grid */
        foreach ($grids as $grid) {
            $gridTitle = $mode === self::MODE_GENERAL
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
            $this->deletePrevious(self::CACHE_LISTING_KEY, $botService);
            Cache::put(self::CACHE_LISTING_KEY, $message, RequesterService::CACHE_DAY);
        }

        return null;
    }
}
