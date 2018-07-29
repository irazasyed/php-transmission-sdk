<?php

namespace Transmission;

use Carbon\CarbonInterval;
use Illuminate\Support\Carbon;

/**
 * Helper
 */
class Helper
{
    /** Used by formatBytes() modes */
    const UNITS_MODE_DECIMAL = 0;
    const UNITS_MODE_BINARY = 1;
    const UNITS_MODE_DATA = 2;

    /**
     * Formats bytes into a human readable string if $format is true, otherwise return $bytes as is.
     *
     * @param int  $bytes
     * @param bool $format Should we suffix symbol? Default: true.
     * @param int  $mode   Should we calculate in binary/speed mode? Default: decimal.
     *
     * @return string
     */
    public static function formatBytes(int $bytes, bool $format = true, int $mode = 0): string
    {
        if (!$format) {
            return $bytes;
        }

        switch ($mode) {
            case static::UNITS_MODE_BINARY: // Binary (Used with memory size formating)
                $base = 1024;
                $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
                break;
            case static::UNITS_MODE_DATA: // Data-rate units
                $base = 1000;
                $units = ['B/s', 'kB/s', 'MB/s', 'GB/s', 'TB/s', 'PB/s', 'EB/s', 'ZB/s', 'YB/s'];
                break;
            default: // Decimal (Disk space)
                $base = 1000;
                $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
                break;
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
            case 'collection':
                return collect(is_array($value) ? $value : (new static)->fromJson($value));
            case 'interval':
                return $value < 1 ? -1 : CarbonInterval::seconds($value)->cascade();
            case 'date':
                return (new static)->asDate($value);
            case 'datetime':
                return (new static)->asDateTime($value);
            case 'timestamp':
                return (new static)->asTimestamp($value);
            case 'size':
                return static::formatBytes($value);
            case 'memory':
                return static::formatBytes($value, true, static::UNITS_MODE_BINARY);
            case 'datarate':
                return static::formatBytes($value, true, static::UNITS_MODE_DATA);
            default:
                return $value;
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

    /**
     * Return a timestamp as unix timestamp.
     *
     * @param  mixed $value
     *
     * @return int
     */
    protected function asTimestamp($value)
    {
        return $this->asDateTime($value)->getTimestamp();
    }
}