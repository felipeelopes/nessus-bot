<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Grid as GridAdapter;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Models\User;
use Application\Services\CommandService;
use Application\Services\Requester\RequesterService;
use Application\Services\Telegram\BotService;
use Application\Strategies\Contracts\UserStrategyContract;
use Application\Strategies\Traits\GridMessage;
use Cache;
use Illuminate\Database\Eloquent\Builder;

class GridListingStrategy implements UserStrategyContract
{
    use GridMessage;

    public const MODE_GENERAL = 'gridShow';

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
                $gridCacheKey = __CLASS__ . '@grid:' . $grid->id;

                $this->deletePrevious($gridCacheKey, $botService);
                Cache::put($gridCacheKey, $gridMessage, RequesterService::CACHE_DAY);
            }

            return true;
        }

        return null;
    }
}
