<?php

declare(strict_types = 1);

namespace Application\Adapters\Ranking;

use Application\Adapters\BaseFluent;
use Application\Services\FormattingService;

/**
 * @property PlayerRanking $ranking    Player ranking.
 * @property int           $level      Player level.
 * @property int[]         $xpBase     Experience base (from, to not inclusive).
 * @property string        $icon       Level icon.
 * @property string        $title      Level title.
 * @property int[]         $titleLevel Level title base (from, to).
 */
class PlayerLevel extends BaseFluent
{
    /**
     * Returns the icon title based on user experience.
     * @return string
     */
    public function getIconTitle(): string
    {
        $titleLevel = ' ';

        if ($this->titleLevel !== [ 1, 1 ]) {
            $titleLevel = FormattingService::toSuperscript((string) $this->titleLevel[0]);
        }

        return sprintf('`%s%s`',
            $this->icon,
            $titleLevel);
    }
}
