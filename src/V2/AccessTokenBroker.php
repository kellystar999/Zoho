<?php

declare(strict_types=1);

namespace Zoho\Crm\V2;

use DateTimeImmutable;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as HttpRequest;
use GuzzleHttp\Psr7\Response as HttpResponse;
use Zoho\Crm\Contracts\AccessTokenBrokerInterface;
use Zoho\Crm\Exceptions\InvalidEndpointException;
use Zoho\Crm\Support\Helper;
use Zoho\Crm\Support\UrlParameters;

class AccessTokenBroker implements AccessTokenBrokerInterface
{
    /** @var string The base URL of the API OAuth 2.0 authorization endpoint used by default */
    public const DEFAULT_ENDPOINT_BASE_URL = 'https://accounts.zoho.com/oauth/v2/';

    /** @var The OAuth 2.0 client ID */
    protected string $clientId;

    /** @var The OAuth 2.0 client secret */
    protected string $clientSecret;

    /** @var The OAuth 2.0 refresh token */
    protected string $refreshToken;

    /** @var The base URL of the API OAuth 2.0 authorization endpoint */
    protected string $endpointBaseUrl = self::DEFAULT_ENDPOINT_BASE_URL;

    /** @var The Guzzle client to make HTTP requests */
    protected GuzzleClient $httpClient;

    /**
     * The constructor.
     *
     * @param string $clientId The client ID
     * @param string $clientSecret The client secret
     * @param string $refreshToken The refresh token
     * @param string|null $endpoint (optional) The authorization endpoint base URL
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        string $refreshToken,
        string $endpoint = null
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->refreshToken = $refreshToken;
        $this->httpClient = new GuzzleClient();

        if (isset($endpoint)) {
            $this->setAuthorizationEndpoint($endpoint);
        }
    }

    /**
     * Set the API OAuth 2.0 authorization endpoint base URL.
     *
     * It will ensure that there is one slash at the end.
     *
     * @param string $endpoint The endpoint base URL
     * @return void
     */
    public function setAuthorizationEndpoint(string $endpoint): void
    {
        // Make sure the endpoint ends with a single slash
        $endpoint = Helper::finishString($endpoint, '/');

        if ($endpoint === '/') {
            throw new InvalidEndpointException();
        }

        $this->endpointBaseUrl = $endpoint;
    }

    /**
     * Get the API OAuth 2.0 authorization endpoint.
     *
     * @return string
     */
    public function getAuthorizationEndpoint(): string
    {
        return $this->endpointBaseUrl;
    }

    /**
     * Request to the OAuth 2.0 authorization server to get a fresh access token.
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    public function requestFreshAccessToken(): HttpResponse
    {
        $parameters = new UrlParameters([
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken
        ]);

        $request = new HttpRequest(
            'POST',
            $this->endpointBaseUrl . 'token',
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            (string) $parameters
        );

        return $this->httpClient->send($request);
    }

    /**
     * @inheritdoc
     */
    public function getAccessTokenWithExpiryDate(): array
    {
        $response = $this->requestFreshAccessToken();
        $response = json_decode((string) $response->getBody(), true);

        $token = $response['access_token'] ?? null;
        $delayInSeconds = $response['expires_in_sec'] ?? $response['expires_in'];
        $expiryDate = (new DateTimeImmutable())->modify("+{$delayInSeconds} seconds");

        return [$token, $expiryDate];
    }
}