<?php

declare(strict_types = 1);

namespace Application\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @property int    $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method $this withTrashed()
 */
class Model extends EloquentModel
{
}
