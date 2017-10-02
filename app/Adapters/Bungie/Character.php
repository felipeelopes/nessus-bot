<?php

declare(strict_types = 1);

namespace Application\Adapters\Bungie;

use Application\Adapters\BaseFluent;
use Carbon\Carbon;

/**
 * @property Carbon $dateLastPlayed Last played.
 * @property int    $characterId    Character id.
 */
class Character extends BaseFluent
{
    /**
     * BaseFluent constructor.
     * @param array|null $attributes
     */
    public function __construct($attributes = null)
    {
        parent::__construct();

        $this->dateLastPlayed = new Carbon(array_get($attributes, 'dateLastPlayed'));
        $this->characterId    = (int) array_get($attributes, 'characterId');
    }
}
