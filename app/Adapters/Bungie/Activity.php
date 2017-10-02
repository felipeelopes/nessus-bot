<?php

declare(strict_types = 1);

namespace Application\Adapters\Bungie;

use Application\Adapters\BaseFluent;
use Carbon\Carbon;

/**
 * @property Carbon $period     Activity period (start).
 * @property int    $instanceId Activity instance ID.
 * @property int[]  $mode       Activity mode.
 */
class Activity extends BaseFluent
{
    /**
     * BaseFluent constructor.
     * @param array|null $attributes
     */
    public function __construct($attributes = null)
    {
        parent::__construct();

        $this->period     = (new Carbon(array_get($attributes, 'period')))->timezone(env('APP_TIMEZONE'));
        $this->instanceId = (int) array_get($attributes, 'activityDetails.instanceId');
        $this->mode       = array_get($attributes, 'activityDetails.mode');
    }
}
