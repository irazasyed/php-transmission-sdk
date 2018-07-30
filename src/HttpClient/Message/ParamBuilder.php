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
                    return static::wrap($value);
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
