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
            })->transform(function ($value) {
                if (is_object($value)) {
                    return $value->toArray();
                } elseif (is_array($value)) {
                    return static::build($value);
                } elseif (is_numeric($value)) {
                    return $value + 0;
                } elseif (is_bool($value)) {
                    return (int)$value;
                } elseif (is_string($value)) { // Encode if it's not UTF-8
                    if (mb_detect_encoding($value, 'auto') !== 'UTF-8') {
                        return mb_convert_encoding($value, 'UTF-8');
                    }
                }

                return $value;
            })->toArray();
    }
}
