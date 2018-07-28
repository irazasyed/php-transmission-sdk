<?php

namespace Transmission;

use Illuminate\Support\Carbon;

/**
 * Helper
 */
class Helper
{
    /**
     * Format Bytes.
     *
     * @param int  $bytes
     * @param bool $format     Should we suffix symbol? Default: true.
     * @param bool $binaryMode Should we calculate in binary mode? Default: false.
     *
     * @return string
     */
    public static function formatBytes(int $bytes, bool $format = true, bool $binaryMode = false): string
    {
        if (!$format) {
            return $bytes;
        }

        if ($binaryMode) {
            $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
            $base = 1024; // Binary
        } else {
            $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            $base = 1000; // Decimal
        }

        $i = 0;

        while ($bytes > $base) {
            $bytes /= $base;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param mixed  $value
     * @param string $type
     *
     * @return mixed
     */
    public static function castAttribute($type, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        switch ($type) {
            case 'int':
            case 'integer':
                return (int)$value;
            case 'real':
            case 'float':
            case 'double':
                return (float)$value;
            case 'string':
                return (string)$value;
            case 'bool':
            case 'boolean':
                return (bool)$value;
            case 'object':
                return (new static)->fromJson($value, true);
            case 'array':
            case 'json':
                return (new static)->fromJson($value);
            case 'collection':
                return collect(is_array($value) ? $value : (new static)->fromJson($value));
            case 'date':
                return (new static)->asDate($value);
            case 'datetime':
                return (new static)->asDateTime($value);
            case 'bytes':
                return static::formatBytes($value);
                break;
            default:
                return $value;
                break;
        }
    }

    /**
     * Encode the given value as JSON.
     *
     * @param  mixed $value
     *
     * @return string
     */
    protected function asJson($value)
    {
        return json_encode($value);
    }

    /**
     * Decode the given JSON back into an array or object.
     *
     * @param  string $value
     * @param  bool   $asObject
     *
     * @return mixed
     */
    protected function fromJson($value, $asObject = false)
    {
        return json_decode($value, !$asObject);
    }

    /**
     * Return a timestamp as DateTime object with time set to 00:00:00.
     *
     * @param  mixed $value
     *
     * @return \Illuminate\Support\Carbon
     */
    protected function asDate($value)
    {
        return $this->asDateTime($value)->startOfDay();
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed $value
     *
     * @return \Illuminate\Support\Carbon
     */
    protected function asDateTime($value)
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof Carbon) {
            return $value;
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        return $value;
    }
}