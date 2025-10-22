<?php

namespace Ashio;

use Ashio\Accounting\Billing;
use Ashio\Accounting\EmailTemplate;
use Ashio\Accounting\ResellerCustomer;
use Ashio\Domain\Domain;
use Ashio\Exception\ApiException;
use Ashio\Exception\AuthenticationException;
use Ashio\Exception\NotFoundException;
use Ashio\Exception\ParameterException;
use Ashio\Exception\ValidationException;
use Ashio\License\License;
use Ashio\SSL\SSL;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class OpenProvider
{
    private Client $httpClient;
    private Credentials $credentials;
    private string $apiToken;
    private ?Domain $domainHandler = null;
    private ?Billing $billingHandler = null;
    private ?EmailTemplate $emailTemplateHandler = null;
    private ?ResellerCustomer $resellerCustomerHandler = null;
    private ?License $licenseHandler = null;
    private ?SSL $sslHandler = null;

    /**
     * OpenProvider constructor.
     *
     * @param string $username OpenProvider Username
     * @param string $password OpenProvider Password
     * @param string $environment Environment: 'production' or 'test' (sandbox)
     * @param Client|null $httpClient
     * @throws GuzzleException
     */
    public function __construct(
        string $username,
        string $password,
        string $environment = 'production',
        ?Client $httpClient = null
    ) {
        $this->setHttpClient($httpClient);
        $this->authenticate($username, $password, $environment);
    }

    /**
     * @param Client|null $httpClient
     * @return void
     */
    public function setHttpClient(?Client $httpClient = null): void
    {
        $this->httpClient = $httpClient ?: new Client([
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => 'OpenProvider/1.0',
            ],
            'allow_redirects' => false,
            'timeout' => 120,
        ]);
    }

    /**
     * Authenticates the user and sets the API token.
     *
     * @param string $username
     * @param string $password
     * @param string $environment
     * @return void
     * @throws GuzzleException
     */
    private function authenticate(string $username, string $password, string $environment = 'production'): void
    {
        // Determine API URL based on environment
        $baseUrl = $environment === 'test' 
            ? 'http://api.sandbox.openprovider.nl:8480/v1beta/'
            : 'https://api.openprovider.eu/v1beta/';
        
        $response = $this->httpClient->post($baseUrl . 'auth/login', [
            'json' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);

        $data = json_decode($response->getBody()->__toString(), true);

        if (($response->getStatusCode() !== 200) || !isset($data['data']['token'])) {
            throw new AuthenticationException('Authentication failed: ' . ($data['desc'] ?? 'Unknown error'));
        }

        $this->apiToken = $data['data']['token'];
        $this->credentials = new Credentials($this->apiToken, $environment);

        // Keep the injected client as-is to allow testing with custom handlers.
    }

    /**
     * @param string $actionPath The resource path you want to request, see more at the documentation.
     * @param array<string, mixed> $params Array filled with request params
     * @param string $method HTTP method used in the request
     *
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws ParameterException If the given field in params is not an array
     */
    private function request(string $actionPath, array $params = [], string $method = 'GET'): ResponseInterface
    {
        $url = $this->credentials->getUrl() . $actionPath;

        $options = [
            'json' => $params,
            'headers' => [
                'Authorization' => $this->apiToken ? ('Bearer ' . $this->apiToken) : null,
            ],
        ];

        // Remove null headers to avoid sending empty header lines
        $options['headers'] = array_filter($options['headers'], static fn ($v) => $v !== null);

        switch ($method) {
            case 'GET':
                return $this->httpClient->get($url, $options);
            case 'POST':
                return $this->httpClient->post($url, $options);
            case 'PUT':
                return $this->httpClient->put($url, $options);
            case 'DELETE':
                return $this->httpClient->delete($url, $options);
            default:
                throw new ParameterException('Invalid HTTP method.');
        }
    }

    /**
     * @param ResponseInterface $response
     * @return array<string, mixed>|string
     */
    private function processRequest(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody()->__toString();
        $result = json_decode($responseBody, true);

        if ($statusCode >= 200 && $statusCode < 300) {
            return json_last_error() === JSON_ERROR_NONE ? $result : $responseBody;
        }

        $errorMessage = $result['desc'] ?? $result['message'] ?? 'An unknown error occurred.';

        switch ($statusCode) {
            case 401:
            case 403:
                throw new AuthenticationException($errorMessage, $statusCode);
            case 404:
                throw new NotFoundException($errorMessage, $statusCode);
            case 422:
                throw new ValidationException($errorMessage, $statusCode);
            default:
                throw new ApiException($errorMessage, $statusCode);
        }
    }

    /**
     * @param string $actionPath
     * @param array<string, mixed> $params
     * @return array<string, mixed>|string
     * @throws GuzzleException
     */
    public function post(string $actionPath, array $params = [])
    {
        $response = $this->request($actionPath, $params, 'POST');

        return $this->processRequest($response);
    }

    /**
     * @param string $actionPath
     * @param array<string, mixed> $params
     * @return array<string, mixed>|string
     * @throws GuzzleException
     */
    public function get(string $actionPath, array $params = [])
    {
        $response = $this->request($actionPath, $params, 'GET');

        return $this->processRequest($response);
    }

    /**
     * @param string $actionPath
     * @param array<string, mixed> $params
     * @return array<string, mixed>|string
     * @throws GuzzleException
     */
    public function put(string $actionPath, array $params = [])
    {
        $response = $this->request($actionPath, $params, 'PUT');

        return $this->processRequest($response);
    }

    /**
     * @param string $actionPath
     * @param array<string, mixed> $params
     * @return array<string, mixed>|string
     * @throws GuzzleException
     */
    public function delete(string $actionPath, array $params = [])
    {
        $response = $this->request($actionPath, $params, 'DELETE');

        return $this->processRequest($response);
    }

    /**
     * @return Billing
     */
    public function billing(): Billing
    {
        if (!$this->billingHandler) {
            $this->billingHandler = new Billing($this);
        }

        return $this->billingHandler;
    }

    /**
     * @return EmailTemplate
     */
    public function emailTemplate(): EmailTemplate
    {
        if (!$this->emailTemplateHandler) {
            $this->emailTemplateHandler = new EmailTemplate($this);
        }

        return $this->emailTemplateHandler;
    }

    /**
     * @return ResellerCustomer
     */
    public function resellerCustomer(): ResellerCustomer
    {
        if (!$this->resellerCustomerHandler) {
            $this->resellerCustomerHandler = new ResellerCustomer($this);
        }

        return $this->resellerCustomerHandler;
    }

    /**
     * @return Domain
     */
    public function domain(): Domain
    {
        if (!$this->domainHandler) {
            $this->domainHandler = new Domain($this);
        }

        return $this->domainHandler;
    }

    /**
     * @return License
     */
    public function license(): License
    {
        if (!$this->licenseHandler) {
            $this->licenseHandler = new License($this);
        }

        return $this->licenseHandler;
    }

    /**
     * @return SSL
     */
    public function ssl(): SSL
    {
        if (!$this->sslHandler) {
            $this->sslHandler = new SSL($this);
        }

        return $this->sslHandler;
    }
}
