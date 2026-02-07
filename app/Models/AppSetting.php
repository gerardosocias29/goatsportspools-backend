<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Get a setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value by key
     */
    public static function setValue($key, $value)
    {
        $setting = self::where('key', $key)->first();

        if ($setting) {
            $setting->value = is_bool($value) ? ($value ? '1' : '0') : $value;
            $setting->save();
            return $setting;
        }

        return null;
    }

    /**
     * Cast value based on type
     */
    protected static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return $value === '1' || $value === 'true' || $value === true;
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Get the typed value attribute
     */
    public function getTypedValueAttribute()
    {
        return self::castValue($this->value, $this->type);
    }
}
