<?php

namespace App\Services;

use App\Models\SiteSetting;

class SiteSettingsService
{
    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return SiteSetting::get($key, $default);
    }

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return SiteSetting
     */
    public function set(string $key, mixed $value): SiteSetting
    {
        return SiteSetting::set($key, $value);
    }

    /**
     * Get the About Us text.
     *
     * @return string|null
     */
    public function getAboutUs(): ?string
    {
        return SiteSetting::getAboutUs();
    }

    /**
     * Set the About Us text.
     *
     * @param string $text
     * @return SiteSetting
     */
    public function setAboutUs(string $text): SiteSetting
    {
        return SiteSetting::setAboutUs($text);
    }

    /**
     * Get the verify on mail changed setting.
     *
     * @return bool
     */
    public function getVerifyOnMailChanged(): bool
    {
        return SiteSetting::getVerifyOnMailChanged();
    }

    /**
     * Set the verify on mail changed setting.
     *
     * @param bool $value
     * @return SiteSetting
     */
    public function setVerifyOnMailChanged(bool $value): SiteSetting
    {
        return SiteSetting::setVerifyOnMailChanged($value);
    }
}
