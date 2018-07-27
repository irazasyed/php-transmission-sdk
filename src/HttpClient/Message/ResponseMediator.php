<?php

namespace Transmission\HttpClient\Message;

use Psr\Http\Message\ResponseInterface;

/**
 * Utilities to parse response headers and content.
 */
class ResponseMediator
{
    /**
     * Check status code for informational response.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public static function isInformational(ResponseInterface $response): bool
    {
        return $response->getStatusCode() >= 100 && $response->getStatusCode() < 200;
    }

    /**
     * Check status code for success.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public static function isSuccess(ResponseInterface $response): bool
    {
        return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    }

    /**
     * Alias of static::isSuccess().
     *
     * @see static::isSuccess()
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public static function isOk(ResponseInterface $response): bool
    {
        return static::isSuccess($response);
    }

    /**
     * Check status code for redirect.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public static function isRedirect(ResponseInterface $response): bool
    {
        return $response->getStatusCode() >= 300 && $response->getStatusCode() < 400;
    }

    /**
     * Check status code for errors.
     *
     * @see static::isSuccess()
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public static function isError(ResponseInterface $response): bool
    {
        return static::isClientError($response) || static::isServerError($response);
    }

    /**
     * Check status code for client error.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public static function isClientError(ResponseInterface $response): bool
    {
        return $response->getStatusCode() >= 400 && $response->getStatusCode() < 500;
    }

    /**
     * Check status code for server error.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public static function isServerError(ResponseInterface $response): bool
    {
        return $response->getStatusCode() >= 500;
    }

    /**
     * Check status code for conflict error.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public static function isConflictError(ResponseInterface $response): bool
    {
        return $response->getStatusCode() === 409;
    }

    /**
     * Return the response body as a string or json array if content type is application/json.
     *
     * @param ResponseInterface $response
     *
     * @return array|string
     */
    public static function getContent(ResponseInterface $response)
    {
        $body = $response->getBody()->__toString();

        if (static::isError($response)) {
            // Transmission returns error messages with HTML.
            return strip_tags($body);
        }

        if (strpos($response->getHeaderLine('Content-Type'), 'application/json') === 0) {
            $content = json_decode($body, true);
            if (JSON_ERROR_NONE === json_last_error()) {
                return $content;
            }
        }

        return $body;
    }
}
