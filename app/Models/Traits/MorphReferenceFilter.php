<?php

declare(strict_types = 1);

namespace Application\Models\Traits;

use Application\Models\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method Builder filterMorphReference($reference, ?string $referenceColumn = null)
 */
trait MorphReferenceFilter
{
    /**
     * Filter by morph reference.
     */
    public function scopeFilterMorphReference(Builder $builder, $reference, ?string $referenceColumn = null): void
    {
        $isModel         = $reference instanceof Model;
        $referenceColumn = $referenceColumn ?? 'reference';

        $builder->where($referenceColumn . '_type', get_class($reference));

        if ($isModel) {
            $builder->where($referenceColumn . '_id', $reference->id);
        }
        else {
            $builder->whereNull($referenceColumn . '_id');
        }
    }
}
