<?php

declare(strict_types = 1);

namespace Application\Models;

use Application\Models\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property Model       $reference        Reference model.
 * @property string      $reference_type   Reference class type.
 * @property string|null $reference_id     Reference class id.
 * @property string      $setting_name     Setting name.
 * @property mixed       $setting_value    Setting value.
 */
class Setting extends Model
{
    use   SoftDeletes;

    /**
     * Model casts.
     * @var string[]
     */
    protected $casts = [
        'setting_value' => 'json',
    ];

    /**
     * Returns the reference model.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
