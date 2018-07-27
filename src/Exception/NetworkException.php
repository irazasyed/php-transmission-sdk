<?php

namespace Transmission\Exception;

/**
 * NetworkException
 */
class NetworkException extends \Exception
{
    const CONFLICT = 409;

    public static $statusCodes = [
        // 4xx: Client Error - The request contains bad syntax or cannot be fulfilled.
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        444 => 'Connection Closed Without Response',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',

        // 5xx: Server Error - The server failed to fulfill an apparently valid request.
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    /**
     * Create Exception by Network Code.
     *
     * @param int         $statusCode
     * @param null|string $message
     *
     * @return static
     */
    public static function createByCode(int $statusCode, string $message = null)
    {
        $errorMessage = static::$statusCodes[$statusCode];

        if (filled($message)) {
            $message = '- ' . $message;
        }

        $message = sprintf('%d: %s %s', $statusCode, $errorMessage, $message);

        return new static($message, $statusCode);
    }
}