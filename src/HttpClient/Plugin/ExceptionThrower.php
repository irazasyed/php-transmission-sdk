<?php

namespace Transmission\HttpClient\Plugin;

use Http\Promise\Promise;
use Http\Client\Common\Plugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Transmission\Exception\NetworkException;
use Transmission\Exception\TransmissionException;
use Transmission\HttpClient\Message\ResponseMediator;

/**
 * A plugin to throw exception based on response status code.
 */
class ExceptionThrower implements Plugin
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        return $next($request)->then(function (ResponseInterface $response) {
            $statusCode = $response->getStatusCode();
            $content = ResponseMediator::getContent($response);

            if (ResponseMediator::isSuccess($response)) {
                if (is_array($content) && isset($content['result']) && 'success' !== $content['result']) {
                    throw new TransmissionException($content['result'], $statusCode);
                }
            } elseif (!ResponseMediator::isConflictError($response) && ResponseMediator::isError($response)) {
                switch ($statusCode) {
                    case 401:
                        $message = 'Invalid Username/Password';
                        break;
                    case 403:
                        $message = 'Your IP Address is Not Whitelisted';
                        break;
                    default:
                        $message = is_array($content) ? $content['result'] : $content;
                        break;
                }

                throw NetworkException::createByCode($statusCode, $message);
            }

            return $response;
        });
    }
}
