<?php

declare(strict_types = 1);

namespace Application\Services;

use Application\Models\Model;
use Application\Models\Setting;

class SettingService
{
    /**
     * Returns a setting service from a model reference.
     * @param object $reference Model reference.
     * @param string $name      Setting name.
     * @return Setting
     */
    public static function fromReference($reference, string $name): Setting
    {
        $isModel = $reference instanceof Model;

        /** @var Setting $settingQuery */
        $settingQuery = Setting::query();
        $settingQuery->filterMorphReference($reference);
        $settingQuery->where('setting_name', $name);

        /** @var Setting $setting */
        $setting = $settingQuery->first();

        if ($setting) {
            return $setting;
        }

        $setting                 = new Setting;
        $setting->reference_type = get_class($reference);
        if ($isModel) {
            $setting->reference_id = $reference->id;
        }
        $setting->setting_name = $name;

        return $setting;
    }
}
