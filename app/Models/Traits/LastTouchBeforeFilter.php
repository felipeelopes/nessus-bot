<?php

declare(strict_types = 1);

namespace Application\Models\Traits;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @method Builder filterLastTouchBefore(string $name, Carbon $reference, ?bool $shouldExists = null)
 */
trait LastTouchBeforeFilter
{
    /**
     * Generate a last touch reference query.
     */
    public function scopeFilterLastTouchBefore(Builder $builder, string $name, Carbon $reference, ?bool $shouldExists = null): void
    {
        $referenceColumn = DB::raw(addcslashes(DB::getTablePrefix() . $this->getTable(), '`') . '.id');

        $builder->where(function (Builder $builder) use ($referenceColumn, $name, $reference, $shouldExists) {
            if ($shouldExists !== true) {
                $builder->whereNotExists(function (QueryBuilder $builder) use ($name, $referenceColumn) {
                    $builder->select('*');
                    $builder->from('settings');
                    $builder->where('reference_type', self::class);
                    $builder->where('reference_id', $referenceColumn);
                    $builder->where('setting_name', $name);
                });
            }

            $builder->orWhereExists(function (QueryBuilder $builder) use ($referenceColumn, $name, $reference) {
                $builder->select('*');
                $builder->from('settings');
                $builder->where('reference_type', self::class);
                $builder->where('reference_id', $referenceColumn);
                $builder->where('setting_name', $name);
                $builder->where('updated_at', '<=', $reference);
            });
        });
    }
}
