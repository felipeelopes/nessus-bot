<?php

declare(strict_types = 1);

namespace Application\Models\Traits;

use Carbon\Carbon;

/**
 * @property-read Carbon|null $deleted_at
 */
trait SoftDeletes
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
}
