<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Adapters\Grid as GridAdapter;
use Application\Models\Grid;
use Application\Models\GridSubscription;
use Application\Models\Model;
use Application\Models\User;
use Application\Services\Telegram\BotService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GridNotifierExecutor extends Executor
{
    /**
     * @inheritdoc
     */
    public function run(?Model $model = null): ?bool
    {
        /** @var Grid $gridsQuery */
        $gridsQuery = Grid::query();
        $gridsQuery->filterNonNotifieds();
        $grids = $gridsQuery->get();

        $botService = BotService::getInstance();

        /** @var Grid $grid */
        foreach ($grids as $grid) {
            $gridAdapter = GridAdapter::fromModel($grid);

            /** @var GridSubscription|Builder $gridSubscribersQuery */
            $gridSubscribersQuery = $grid->subscribers();
            $gridSubscribersQuery->with([ 'gamertag.user' ]);
            $gridSubscribersQuery->filterByPosition(GridSubscription::POSITION_TITULAR);
            $gridSubscribers = $gridSubscribersQuery->get();

            /** @var User[]|Collection $users */
            $users = $gridSubscribers->pluck('gamertag.user');

            foreach ($users as $user) {
                $botService->createMessage()
                    ->setReceiver($user->user_number)
                    ->appendMessage(trans('GridSubscription.notifyMessage', [
                        'command' => '/' . trans('Command.commands.gridShowShortCommand') . $grid->id,
                        'title'   => $gridAdapter->getTitle(),
                        'hours'   => $gridAdapter->getTiming(false),
                        'minutes' => $gridAdapter->getMinutesDistance(),
                    ]))
                    ->publish();
            }

            $grid->grid_status = Grid::STATUS_GATHERING;
            $grid->notified_at = Carbon::now();
            $grid->save();
        }

        return true;
    }
}
