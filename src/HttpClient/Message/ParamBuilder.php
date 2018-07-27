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
                    return array_wrap($value);
                }

                return $value;
            })->toArray();
    }
}
