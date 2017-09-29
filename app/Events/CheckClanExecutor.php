<?php

declare(strict_types = 1);

namespace Application\Events;

use Application\Exceptions\Executor\KeepWorkingException;
use Application\Models\Model;
use Application\Models\User;
use Application\Services\Bungie\BungieService;
use Application\Services\SettingService;
use Application\Services\Telegram\BotService;
use Carbon\Carbon;
use RuntimeException;

class CheckClanExecutor extends Executor
{
    private const NEXT_CLAN_CHECKUP = 'nextClanCheckup';

    /**
     * @inheritdoc
     */
    public function run(?Model $model = null): ?bool
    {
        $now = Carbon::now();

        /** @var User $userQuery */
        $userQuery = User::query();
        $userQuery->has('gamertag');
        $userQuery->where('created_at', '<=', Carbon::now()->subHours(12));
        $userQuery->filterLastTouchBefore(self::NEXT_CLAN_CHECKUP, $now);
        $userQuery->inRandomOrder();
        $user = $userQuery->first();

        if (!$user || !$user->gamertag->bungie_membership) {
            return true;
        }

        try {
            $clanFromMember = BungieService::getInstance()->getClanFromMember($user->gamertag->bungie_membership);
        }
        catch (RuntimeException $runtimeException) {
            throw new KeepWorkingException;
        }

        $settings = SettingService::fromReference($user, self::NEXT_CLAN_CHECKUP);

        if ($clanFromMember && in_array($clanFromMember->groupId, explode(',', env('NBOT_CLANS')), false)) {
            $settings->updated_at = Carbon::now()->addDays(1);
            $settings->save();

            throw new KeepWorkingException;
        }

        $clanMessage = null;

        if (!$clanFromMember) {
            $clanMessage = trans('CheckClan.clanEmpty');
        }
        else {
            $clanMessage = trans('CheckClan.clasRivals', [
                'clan' => $clanFromMember->name,
                'sign' => $clanFromMember->clanCallsign,
                'days' => $clanFromMember->joinDate->diffInDays($now),
            ]);
        }

        BotService::getInstance()->createMessage()
            ->appendMessage(trans('CheckClan.clanUnassigned', [
                'fullname' => $user->getFullname(),
                'gamertag' => $user->gamertag->gamertag_value,
                'message'  => $clanMessage,
                'days'     => $user->created_at->diffInDays($now),
            ]))
            ->forceAdministrative()
            ->publish();

        $settings->updated_at = Carbon::now()->addDays(1);
        $settings->save();

        throw new KeepWorkingException;
    }
}
