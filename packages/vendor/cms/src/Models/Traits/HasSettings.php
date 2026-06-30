<?php

namespace Vendor\Cms\Models\Traits;

use Vendor\Cms\Models\Setting;

trait HasSettings
{
    /**
     * Get settings relationship.
     */
    public function settings()
    {
        return $this->hasMany(Setting::class, 'etablissement_id');
    }

    /**
     * Get a setting value.
     */
    public function getSetting($key, $default = null, $group = 'general')
    {
        $setting = $this->settings()
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        if (!$setting && $group === 'general') {
            if ($key === 'contact_email') {
                $setting = $this->settings()
                    ->where('group', $group)
                    ->where('key', 'email')
                    ->first();
            } elseif ($key === 'email') {
                $setting = $this->settings()
                    ->where('group', $group)
                    ->where('key', 'contact_email')
                    ->first();
            }
        }

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value.
     */
    public function setSetting($key, $value, $group = 'general', $options = null)
    {
        return Setting::updateOrCreate(
            [
                'etablissement_id' => $this->id,
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => $value,
                'options' => $options,
            ]
        );
    }

    /**
     * Get all settings for a group.
     */
    public function getSettingsGroup($group = 'general')
    {
        return $this->settings()
            ->where('group', $group)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Delete a setting.
     */
    public function deleteSetting($key, $group = 'general')
    {
        return $this->settings()
            ->where('group', $group)
            ->where('key', $key)
            ->delete();
    }
}
