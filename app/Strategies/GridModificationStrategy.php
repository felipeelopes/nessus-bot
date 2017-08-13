<?php

declare(strict_types = 1);

namespace Application\Strategies;

use Application\Adapters\Grid as GridAdapter;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Models\User;
use Application\Services\CommandService;
use Application\Services\PredefinitionService;
use Application\Services\SessionService;
use Application\Services\Telegram\BotService;
use Application\SessionsProcessor\GridModification\InitializationMoment;
use Application\Strategies\Contracts\UserStrategyContract;
use Application\Strategies\Traits\GridMessage;
use Illuminate\Database\Eloquent\Builder;

class GridModificationStrategy implements UserStrategyContract
{
    use GridMessage;

    private const MODE_OWNEDS = 'myGridShow';

    /**
     * @inheritdoc
     */
    public function process(?User $user, Update $update): ?bool
    {
        if ($user === null) {
            return null;
        }

        if ($update->message->isCommand(CommandService::COMMAND_MY_GRIDS)) {
            $botService = BotService::getInstance();

            /** @var Grid|Builder $gridsQuery */
            $gridsQuery = Grid::query();
            $gridsQuery->with('subscribers');
            $gridsQuery->filterOwneds($user);
            $gridsQuery->filterAvailables();
            $grids = $gridsQuery->get()->sort(function (Grid $gridA, Grid $gridB) {
                return ($gridA->getStatusCode() <=> $gridB->getStatusCode())
                    ?: $gridA->grid_timing->gte($gridB->grid_timing);
            });

            $this->sendGridListing($update, $botService, $grids, self::MODE_OWNEDS);

            return true;
        }

        $sessionService = SessionService::getInstance();
        $sessionService->setInitialMoment(InitializationMoment::class);

        return $sessionService->run($update);
    }
}
