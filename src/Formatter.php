<?php

namespace Transmission;

use Carbon\CarbonInterval;
use Illuminate\Support\Carbon;

/**
 * Formatter.
 */
class Formatter
{
    /** Used by formatBytes() modes */
    public const UNITS_MODE_DECIMAL = 0;
    public const UNITS_MODE_BINARY = 1;
    /** Speed */
    public const SPEED_KBPS = 1000;

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

        if ($mode === static::UNITS_MODE_BINARY) { // Binary (Used with memory size formating)
            $base = 1024;
            $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        } else { // Decimal (Disk space)
            $base = 1000;
            $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        }

        $i = 0;

        while ($bytes > $base) {
            $bytes /= $base;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Speed Bps.
     *
     * @param $Bps
     *
     * @return string
     */
    public static function speedBps($Bps): string
    {
        return static::speed(static::bpsToKBps($Bps));
    }

    /**
     * Bps to KBps.
     *
     * @param $Bps
     *
     * @return float
     */
    public static function bpsToKBps($Bps): float
    {
        return floor($Bps / static::SPEED_KBPS);
    }

    /**
     * Format KBps to Data-rate units.
     *
     * @param $KBps
     *
     * @return string
     */
    public static function speed($KBps): string
    {
        $speed = $KBps;

        if ($speed <= 999.95) { // 0 KBps to 999 K
            return static::trunicateNumber($speed, 0).' KB/s';
        }

        $speed /= static::SPEED_KBPS;

        if ($speed <= 99.995) { // 1 M to 99.99 M
            return static::trunicateNumber($speed, 2).' MB/s';
        }
        if ($speed <= 999.95) { // 100 M to 999.9 M
            return static::trunicateNumber($speed, 1).' MB/s';
        }

        // insane speeds
        $speed /= static::SPEED_KBPS;

        return static::trunicateNumber($speed, 2).' GB/s';
    }

    /**
     * Trunicate a number to the given decimal points.
     *
     * @param string $number
     * @param int    $decimals
     *
     * @return string
     */
    public static function trunicateNumber($number, int $decimals = 2): string
    {
        return bcdiv($number, 1, $decimals);
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
        if ($value === null) {
            return $value;
        }

        switch ($type) {
            case 'collection':
                return collect(\is_array($value) ? $value : (new static())->fromJson($value));
            case 'interval':
                return $value < 1 ? -1 : CarbonInterval::seconds($value)->cascade();
            case 'date':
                return (new static())->asDate($value);
            case 'datetime':
                return (new static())->asDateTime($value);
            case 'timestamp':
                return (new static())->asTimestamp($value);
            case 'size':
                return static::formatBytes($value);
            case 'memory':
                return static::formatBytes($value, true, static::UNITS_MODE_BINARY);
            case 'datarate':
                return static::speedBps($value);
            default:
                return $value;
        }
    }

    /**
     * Encode the given value as JSON.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function asJson($value): string
    {
        return json_encode($value);
    }

    /**
     * Decode the given JSON back into an array or object.
     *
     * @param string $value
     * @param bool   $asObject
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
     * @param mixed $value
     *
     * @return \Illuminate\Support\Carbon
     */
    protected function asDate($value): Carbon
    {
        return $this->asDateTime($value)->startOfDay();
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param mixed $value
     *
     * @return \Illuminate\Support\Carbon
     */
    protected function asDateTime($value): Carbon
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
     * @param mixed $value
     *
     * @return int
     */
    protected function asTimestamp($value): int
    {
        return $this->asDateTime($value)->getTimestamp();
    }
}
