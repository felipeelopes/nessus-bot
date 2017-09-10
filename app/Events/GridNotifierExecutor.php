<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Adapters\Grid as GridAdapter;
use Application\Models\Grid;
use Application\Models\GridSubscription;
use Application\Models\User;
use Application\Services\Telegram\BotService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class GridNotifierExecutor extends Executor
{
    /**
     * @inheritdoc
     */
    public function run(): ?bool
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
            $gridSubscribers = $gridSubscribersQuery->get();

            foreach ($gridSubscribers as $gridSubscriber) {
                /** @var User $user */
                $user = $gridSubscriber->gamertag->user;

                $observations = trans('GridSubscription.observation' . Str::ucfirst($gridSubscriber->subscription_position));

                $botService->createMessage()
                    ->setReceiver($user->user_number)
                    ->appendMessage(trans('GridSubscription.notifyMessage', [
                        'position'     => GridSubscription::getPositionText($gridSubscriber->subscription_position),
                        'command'      => '/' . trans('Command.commands.gridShowShortCommand') . $grid->id,
                        'title'        => $gridAdapter->getTitle(),
                        'hours'        => $gridAdapter->getTiming(false),
                        'minutes'      => $gridAdapter->getMinutesDistance(),
                        'observations' => $observations,
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
