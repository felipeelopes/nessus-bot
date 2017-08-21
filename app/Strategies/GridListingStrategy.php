<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Models\User;
use Application\Services\CommandService;
use Application\Services\Telegram\BotService;
use Application\Strategies\Contracts\UserStrategyContract;
use Application\Strategies\Traits\GridMessage;
use Illuminate\Database\Eloquent\Builder;

class GridListingStrategy implements UserStrategyContract
{
    use GridMessage;

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
                return ($gridA->getStatusCode() <=> $gridB->getStatusCode())
                    ?: $gridB->isToday() <=> $gridA->isToday()
                        ?: $gridB->grid_timing->lt($gridA->grid_timing);
            });

            $this->sendGridListing($update, $botService, $grids);

            return true;
        }

        return null;
    }
}
