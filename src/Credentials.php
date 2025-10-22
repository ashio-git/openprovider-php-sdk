<?php

namespace Ashio;

class Credentials
{
    private string $token;
    private string $url;

    /**
     * Credentials constructor.
     * @param string $token
     * @param string $environment Environment: 'production' or 'test'
     */
    public function __construct(string $token, string $environment = 'production')
    {
        $this->token = $token;
        // Set URL based on environment
        $this->url = $environment === 'test'
            ? "http://api.sandbox.openprovider.nl:8480/v1beta/"
            : "https://api.openprovider.eu/v1beta/";
    }

    public function __toString()
    {
        return sprintf(
            '[Host: %s], [Token: %s].',
            $this->url,
            $this->token
        );
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }
}
