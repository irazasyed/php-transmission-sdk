<?php

namespace Transmission\HttpClient\Plugin;

use Transmission\Exception\NetworkException;
use Transmission\Exception\RuntimeException;
use Transmission\HttpClient\Message\ResponseMediator;
use Http\Client\Common\Plugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A plugin to throw exception based on response status code.
 */
class ExceptionThrower implements Plugin
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        return $next($request)->then(function (ResponseInterface $response) {
            if (!ResponseMediator::isConflictError($response) && ResponseMediator::isError($response)) {
                $content = ResponseMediator::getContent($response);

                if (is_array($content) && isset($content['result'])) {
                    throw new RuntimeException($content['result'], $response->getStatusCode());
                }

                switch ($response->getStatusCode()) {
                    case 401:
                        $message = 'Invalid Username/Password';
                        break;
                    case 403:
                        $message = 'Your IP Address is Not Whitelisted';
                        break;
                    default:
                        $message = $content;
                        break;
                }

                throw NetworkException::createByCode($response->getStatusCode(), $message);
            }

            return $response;
        });
    }
}
