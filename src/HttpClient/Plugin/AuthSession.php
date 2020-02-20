<?php

namespace Transmission\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

/**
 * Add Session ID to the Request.
 */
class AuthSession implements Plugin
{
    /** @var string */
    private $sessionId;

    /**
     * @param string $sessionId
     */
    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        if (filled($this->sessionId)) {
            $request = $request->withHeader('X-Transmission-Session-Id', $this->sessionId);
        }

        return $next($request);
    }
}
