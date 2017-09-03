<?php

declare(strict_types = 1);

namespace Application\Models\Observers;

use Application\Models\Model;
use Application\Models\Setting;

abstract class Observer
{
    /**
     * Delete setting references.
     */
    protected static function deleteSettings(Model $model): void
    {
        /** @var Setting $settingsQuery */
        $settingsQuery = Setting::query();
        $settingsQuery->filterMorphReference($model);
        $settings = $settingsQuery->get();

        foreach ($settings as $setting) {
            $setting->forceDelete();
        }
    }

    /**
     * Observe after create a new model.
     * @param Model $model
     */
    public function created(Model $model): void
    {
    }

    /**
     * Observe on creating a new model.
     * @param Model $model
     */
    public function creating(Model $model): void
    {
    }

    /**
     * Observe after delete a model.
     * @param Model $model
     */
    public function deleted(Model $model): void
    {
    }

    /**
     * Observe on deleting a model.
     * @param Model $model
     */
    public function deleting(Model $model): void
    {
    }

    /**
     * Observe after restore a model.
     * @param Model $model
     */
    public function restored(Model $model): void
    {
    }

    /**
     * Observe on restoring a model.
     * @param Model $model
     */
    public function restoring(Model $model): void
    {
    }

    /**
     * Observe after save a model.
     * @param Model $model
     */
    public function saved(Model $model): void
    {
    }

    /**
     * Observe on saving a model.
     * @param Model $model
     */
    public function saving(Model $model): void
    {
    }

    /**
     * Observe after update a model.
     * @param Model $model
     */
    public function updated(Model $model): void
    {
    }

    /**
     * Observe on updating a model.
     * @param Model $model
     */
    public function updating(Model $model): void
    {
    }
}
