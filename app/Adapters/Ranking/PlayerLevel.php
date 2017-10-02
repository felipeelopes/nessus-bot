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
    public function getIconTitle(?bool $full = null): string
    {
        $titleLevel = ' ';

        if ($this->titleLevel !== [ 1, 1 ]) {
            $titleLevel = $this->titleLevel[0];
        }

        if ($full === true) {
            return sprintf('%s %s #%u', $this->icon, $this->title, $titleLevel);
        }

        return sprintf('%s%s', $this->icon, FormattingService::toSuperscript((string) $titleLevel));
    }

    /**
     * Returns how much experiences is need to next level.
     * @return int|null
     */
    public function getNextExperience(): ?int
    {
        if ($this->xpBase[1] === PHP_INT_MAX) {
            return null;
        }

        return (int) ($this->xpBase[1] - $this->ranking->player_experience);
    }

    /**
     * Get percent based on experience.
     * @return float
     */
    public function getPercent(): float
    {
        return ($this->ranking->player_experience - $this->xpBase[0]) / ($this->xpBase[1] - $this->xpBase[0]);
    }
}
