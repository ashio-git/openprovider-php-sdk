<?php

namespace Vexura;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Vexura\Accounting\Billing;
use Vexura\Accounting\EmailTemplate;
use Vexura\Accounting\ResellerCustomer;
use Vexura\Domain\Domain;
use Vexura\Exception\AuthenticationException;
use Vexura\Exception\ApiException;
use Vexura\Exception\NotFoundException;
use Vexura\Exception\ValidationException;
use Vexura\License\License;
use Vexura\SSL\SSL;

class OpenProvider
{
    private $httpClient;
    private $credentials;
    private $apiToken;
    private $domainHandler;
    private $billingHandler;
    private $emailTemplateHandler;
    private $resellerCustomerHandler;
    private $licenseHandler;
    private $sslHandler;

    /**
     * OpenProvider constructor.
     *
     * @param string $username OpenProvider Username
     * @param string $password OpenProvider Password
     * @param Client|null $httpClient
     * @throws GuzzleException
     */
    public function __construct(
        string $username,
        string $password,
        Client $httpClient = null
    ) {
        $this->setHttpClient($httpClient);
        $this->authenticate($username, $password);
    }

    /**
     * @param Client|null $httpClient
     */
    public function setHttpClient(Client $httpClient = null)
    {
        $this->httpClient = $httpClient ?: new Client([
            'http_errors' => false,
            'headers'     => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent'   => 'OpenProvider/1.0',
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
     * @throws GuzzleException
     */
    private function authenticate(string $username, string $password)
    {
        $response = $this->httpClient->post('https://api.openprovider.eu/v1beta/auth/login', [
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
        $this->credentials = new Credentials($this->apiToken);

        // Keep the injected client as-is to allow testing with custom handlers.
    }

    /**
     * @param string $actionPath The resource path you want to request, see more at the documentation.
     * @param array $params Array filled with request params
     * @param string $method HTTP method used in the request
     *
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws ParameterException If the given field in params is not an array
     */
    private function request(string $actionPath, array $params = [], string $method = 'GET'): ResponseInterface
    {
        $url = $this->credentials->getUrl() . $actionPath;

        if (!is_array($params)) {
            throw new ParameterException('Parameters must be an array.');
        }

        $options = [
            'json'   => $params,
            'headers' => [
                'Authorization' => $this->apiToken ? ('Bearer ' . $this->apiToken) : null,
            ],
        ];

        // Remove null headers to avoid sending empty header lines
        if (isset($options['headers'])) {
            $options['headers'] = array_filter($options['headers'], static fn($v) => $v !== null);
        }

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
     * @return array|string
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
     * @throws GuzzleException
     */
    public function post($actionPath, $params = [])
    {
        $response = $this->request($actionPath, $params, 'POST');
        return $this->processRequest($response);
    }

    /**
     * @throws GuzzleException
     */
    public function get($actionPath, $params = [])
    {
        $response = $this->request($actionPath, $params, 'GET');
        return $this->processRequest($response);
    }

    /**
     * @throws GuzzleException
     */
    public function put($actionPath, $params = [])
    {
        $response = $this->request($actionPath, $params, 'PUT');
        return $this->processRequest($response);
    }

    /**
     * @throws GuzzleException
     */
    public function delete($actionPath, $params = [])
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
