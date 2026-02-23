<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SiteSetting extends Model
{
    /** @use HasFactory<\Database\Factories\SiteSettingFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public static function set(string $key, mixed $value): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get the About Us text.
     *
     * @return string|null
     */
    public static function getAboutUs(): ?string
    {
        return static::get('about_us_text');
    }

    /**
     * Set the About Us text.
     *
     * @param string $text
     * @return static
     */
    public static function setAboutUs(string $text): static
    {
        return static::set('about_us_text', $text);
    }

    /**
     * Get the verify on mail changed setting.
     *
     * @return bool
     */
    public static function getVerifyOnMailChanged(): bool
    {
        return (bool) static::get('verify_on_mail_changed', false);
    }

    /**
     * Set the verify on mail changed setting.
     *
     * @param bool $value
     * @return static
     */
    public static function setVerifyOnMailChanged(bool $value): static
    {
        return static::set('verify_on_mail_changed', $value ? '1' : '0');
    }
}
