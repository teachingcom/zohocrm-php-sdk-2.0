<?php

namespace com\zoho\crm\api;

use GuzzleHttp\Client;

/**
 * The Builder class to build a Guzzle REST client.
 */
class ClientBuilder
{
    private $enableSSLVerification = true;
    private $connectionTimeout = 0;
    private $timeout = 0;
    /** @var string|null */
    private $proxy;

    /**
     * This is a setter method to set enableSSLVerification.
     */
    public function sslVerification(bool $enableSSLVerification): self
    {
        $this->enableSSLVerification = $enableSSLVerification;

        return $this;
    }

    /**
     * This is a setter method to set connectionTimeout.
     * @param int $connectionTimeout A int number of seconds to wait while trying to connect.
     */
    public function connectionTimeout(int $connectionTimeout): self
    {
        $this->connectionTimeout = max($connectionTimeout, 0);

        return $this;
    }

    /**
     * This is a setter method to set timeout.
     * @param int $timeout A int maximum number of seconds to allow cURL functions to execute.
     */
    public function timeout(int $timeout): self
    {
        $this->timeout = max($timeout, 0);

        return $this;
    }

    /**
     * Pass a string to specify an HTTP proxy, or an array to specify different proxies for different protocols.
     * @param string|array $proxy Pass a string to specify a proxy for all protocols, like `'http://localhost:8125'`.
     *     Pass an associative array to specify HTTP proxies for specific URI schemes (i.e., "http", "https"). Provide
     *     a `no` key value pair to provide a list of host names that should not be proxied to.
     */
    public function proxy($proxy): self
    {
        $this->proxy = $proxy;

        return $this;
    }

    /**
     * The method to build the Guzzle REST Client instance.
     */
    public function build(): Client
    {
        return new Client([
            'connection_timeout' => $this->connectionTimeout,
            'proxy' => $this->proxy,
            'timeout' => $this->timeout,
            'verify' => $this->enableSSLVerification,
        ]);
    }
}
