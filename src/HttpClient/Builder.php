<?php

namespace Transmission\HttpClient;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClientFactory;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A builder that builds the API client.
 * This will allow you to fluently add and remove plugins.
 *
 * Based on the original code written by Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Builder
{
    /**
     * The object that sends HTTP messages.
     *
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * A HTTP client with all our plugins.
     *
     * @var ClientInterface
     */
    private $pluginClient;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * True if we should create a new Plugin client at next request.
     *
     * @var bool
     */
    private $httpClientModified = true;

    /**
     * @var Plugin[]
     */
    private $plugins = [];

    /**
     * @param ClientInterface $httpClient The client to send requests with.
     */
    public function __construct(ClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestBuilder = new RequestBuilder();
    }

    /**
     * @return ClientInterface
     */
    public function getHttpClient(): ClientInterface
    {
        if ($this->httpClientModified) {
            $this->httpClientModified = false;

            $this->pluginClient = (new PluginClientFactory())->createClient($this->httpClient, $this->plugins);
        }

        return $this->pluginClient;
    }

    /**
     * Sets the http client.
     *
     * @param ClientInterface $httpClient
     *
     * @return Builder
     */
    public function setHttpClient(ClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @return RequestBuilder
     */
    public function getRequestBuilder(): RequestBuilder
    {
        return $this->requestBuilder;
    }

    /**
     * @param RequestBuilder $requestBuilder
     *
     * @return Builder
     */
    public function setRequestBuilder(RequestBuilder $requestBuilder): self
    {
        $this->requestBuilder = $requestBuilder;

        return $this;
    }

    /**
     * Add a new plugin to the end of the plugin chain.
     *
     * @param Plugin $plugin
     *
     * @return Builder
     */
    public function addPlugin(Plugin $plugin): self
    {
        $this->plugins[] = $plugin;
        $this->httpClientModified = true;

        return $this;
    }

    /**
     * Remove a plugin by its fully qualified class name (FQCN).
     *
     * @param string $fqcn
     *
     * @return Builder
     */
    public function removePlugin($fqcn): self
    {
        foreach ($this->plugins as $idx => $plugin) {
            if ($plugin instanceof $fqcn) {
                unset($this->plugins[$idx]);
                $this->httpClientModified = true;
            }
        }

        return $this;
    }

    /**
     * @param string $method
     * @param        $uri
     * @param array  $headers
     * @param null   $body
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     *
     * @return ResponseInterface
     */
    public function send(string $method, $uri, array $headers = [], $body = null): ResponseInterface
    {
        return $this->sendRequest($this->requestBuilder->create(
            $method,
            $uri,
            $headers,
            $body
        ));
    }

    /**
     * @param RequestInterface $request
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     *
     * @return ResponseInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->getHttpClient()->sendRequest($request);
    }
}
