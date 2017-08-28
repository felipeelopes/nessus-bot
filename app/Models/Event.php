<?php

declare(strict_types = 1);

namespace Application\Models;

use Application\Events\Executor;
use Application\Models\Traits\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property Model       $reference        Reference model.
 * @property string|null $reference_type   Reference class type.
 * @property string|null $reference_id     Reference class id.
 * @property string      $event_executor   Class that will handle this event (instance of Events\Executor).
 * @property Carbon      $event_execution  When event should be executed.
 * @property Carbon      $event_executed   When event was executed successfully.
 * @property int         $event_failures   Count of failures when running this event.
 *
 * @method $this filterPendings()
 */
class Event extends Model
{
    use  SoftDeletes;

    public const FAILURE_LIMIT = 3;

    /**
     * Returns the Executor from this Event.
     * @return Executor
     */
    public function executor(): Executor
    {
        return new $this->event_executor;
    }

    /**
     * Returns if this event has been failed.
     * @return bool
     */
    public function hasFailed(): bool
    {
        return !$this->hasSuccess() &&
               $this->event_failures !== 0;
    }

    /**
     * Returns if this event was executed successfully.
     * @return bool
     */
    public function hasSuccess(): bool
    {
        return $this->event_executed !== null;
    }

    /**
     * Returns the reference model.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Filter pending events.
     * @param Builder $builder Builder instance.
     */
    public function scopeFilterPendings(Builder $builder)
    {
        $builder->whereNull('event_executed');
        $builder->where('event_failures', '<=', self::FAILURE_LIMIT);
        $builder->where('event_execution', '<=', Carbon::now());
    }
}
