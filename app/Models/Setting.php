<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private static array $defaults = [
        'company_name' => 'My Business',
        'company_address' => '',
        'company_phone' => '',
        'company_email' => '',
        'company_logo' => '',
        'currency_symbol' => '৳',
        'invoice_footer' => 'Thank you for your business!',
        'sms_enabled' => '0',
        'sms_provider' => '',
        'sms_api_key' => '',
        'sms_sender_id' => '',
        'loyalty_enabled' => '0',
        'loyalty_points_per_100' => '1',
        'loyalty_redeem_value' => '1',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::all()->pluck('value', 'key');

        return $all->get($key, $default ?? (static::$defaults[$key] ?? null));
    }

    public static function getAll(): array
    {
        $saved = static::all()->pluck('value', 'key')->toArray();

        return array_merge(static::$defaults, $saved);
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, static::$defaults)) {
                static::set($key, $value);
            }
        }
    }
}
