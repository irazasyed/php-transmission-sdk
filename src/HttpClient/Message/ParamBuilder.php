<?php

namespace Transmission\HttpClient\Message;

/**
 * ParamBuilder.
 */
final class ParamBuilder
{
    /**
     * Sanitize and build params.
     *
     * @param array $params
     *
     * @return array
     */
    public static function build($params): array
    {
        return collect($params)
            ->reject(function ($value) {
                return blank($value);
            })->transform(function ($value) {
                if (is_object($value)) {
                    return $value->toArray();
                }

                if (is_array($value)) {
                    return static::build($value);
                }

                if (is_numeric($value)) {
                    return $value + 0;
                }

                if (is_bool($value)) {
                    return (int) $value;
                }

                if (is_string($value) && mb_detect_encoding($value, 'auto') !== 'UTF-8') {
                    return mb_convert_encoding($value, 'UTF-8');
                }

                return $value;
            })->toArray();
    }
}
