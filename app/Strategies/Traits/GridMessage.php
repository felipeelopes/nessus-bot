<?php

declare(strict_types = 1);

namespace Application\Strategies\Traits;

use Application\Adapters\Predefinition\OptionItem;
use Application\Adapters\Telegram\Update;
use Application\Models\Grid;
use Application\Services\CommandService;
use Application\Services\FormattingService;
use Application\Services\Telegram\BotService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait GridMessage
{
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
     * Send grid listing to chat.
     * @param Update            $update     Update instace.
     * @param BotService        $botService Bot Service instance.
     * @param Collection|Grid[] $grids      Grids.
     * @return bool|null
     * @throws \Exception
     */
    private function sendGridListing(Update $update, BotService $botService, Collection $grids): ?bool
    {
        if ($grids->isEmpty()) {
            $messageTrans = $update->message->isCommand(CommandService::COMMAND_LIST_GRIDS)
                ? trans('GridListing.isEmpty')
                : trans('GridListing.isEmptyYour');

            $botService->createMessage($update->message)
                ->appendMessage($messageTrans)
                ->setOptions([ OptionItem::fromCommand(CommandService::COMMAND_NEW_GRID) ])
                ->unduplicate(__CLASS__ . '@' . __FUNCTION__)
                ->publish();

            return true;
        }

        $currentTitle = null;
        $result       = null;

        /** @var Grid $grid */
        foreach ($grids as $grid) {
            $gridTitle = trans('GridListing.titleBase', [
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

            $countPlayers = $grid->countPlayers();
            $gridPlayers  = $grid->grid_players;

            $result .= trans('GridListing.item', [
                'timing'     => $grid->grid_timing->format('H:i'),
                'command'    => '/' . trans('Command.commands.gridShowShortCommand') . $grid->id,
                'players'    => $countPlayers,
                'maxPlayers' => $gridPlayers,
                'reserves'   => FormattingService::toSuperscript((string) $grid->countReserves()),
                'title'      => FormattingService::ellipsis($grid->grid_title, 20),
                'subtitle'   => $gridSubtitle,
                'incomplete' => $countPlayers < $gridPlayers
                    ? trans('GridListing.incompleteIcon')
                    : null,
            ]);
        }

        $botService->createMessage($update->message)
            ->appendMessage($result)
            ->setOptions([ OptionItem::fromCommand(CommandService::COMMAND_NEW_GRID) ])
            ->unduplicate(__CLASS__ . '@' . __FUNCTION__)
            ->publish();

        return null;
    }
}
