<?php

declare(strict_types = 1);

namespace Application\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int    $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Model extends EloquentModel
{
    use SoftDeletes;
}
