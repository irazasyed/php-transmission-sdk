<?php

namespace Transmission\HttpClient\Message;

/**
 * ParamBuilder
 */
final class ParamBuilder
{
    /**
     * Sanitize and build params.
     *
     * @param array $params
     *
     * @return string
     */
    public static function build($params)
    {
        return collect($params)
            ->reject(function ($value) {
                return blank($value);
            })->transform(function ($value, $key) {
                if ('ids' === $key) {
                    return $value !== 'recently-active' ? static::wrap($value) : $value;
                }

                if (is_string($value)) { // Encode if it's not UTF-8
                    if (mb_detect_encoding($value, 'auto') !== 'UTF-8') {
                        return mb_convert_encoding($value, 'UTF-8');
                    }
                }

                return $value;
            })->toArray();
    }

    /**
     * If the given value is not an array and not null, wrap it in one.
     * And typehint all values to integer. Primarily used for ids.
     *
     * @param $value
     *
     * @return array
     */
    protected static function wrap($value)
    {
        if (is_null($value)) {
            return [];
        }

        return !is_array($value) ? [(int)$value] : array_map('intval', $value);
    }
}
