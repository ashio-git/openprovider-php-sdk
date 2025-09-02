<?php

namespace Vexura;

class Credentials
{
    private $token;
    private $url;

    /**
     * Credentials constructor.
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
        $this->url = "https://api.openprovider.eu/v1beta/"; // Updated URL
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
