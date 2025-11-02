<?php

use App\Models\Setting;
use Morilog\Jalali\Jalalian;

if (!function_exists('settings')) {
    /**
     * Get setting value by key or return all settings
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function settings($key = null, $default = null)
    {
        try {
            $settings = Setting::instance();

            if ($key === null) {
                return $settings;
            }

            return $settings->getAttribute($key) ?? $default;
        } catch (\Exception $e) {
            // Return default if table doesn't exist (during migration)
            return $default;
        }
    }
}

if (!function_exists('jdate')) {
    /**
     * Format a Carbon date/time to Jalali using Morilog\Jalali
     */
    function jdate($date): Jalalian
    {
        return Jalalian::fromDateTime($date);
    }
}
