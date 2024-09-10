<?php

namespace App\Library\Economy\Payment;

use App\Entity\GatewayCheckout;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaypalGateway implements GatewayInterface
{
    public const PAYPAL_API_ADDRESS_LIVE = 'https://api-m.paypal.com';
    public const PAYPAL_API_ADDRESS_SANDBOX = 'https://api-m.sandbox.paypal.com';

    private CacheInterface $cache;

    public function __construct(
        private string $appEnv,
        private string $paypalClientId,
        private string $paypalClientSecret,
        private HttpClientInterface $httpClient,
    ) {
        $this->cache = new FilesystemAdapter();

        $httpOptions = new HttpOptions();
        $httpOptions->setBaseUri(
            $appEnv === 'prod'
                ? self::PAYPAL_API_ADDRESS_LIVE
                : self::PAYPAL_API_ADDRESS_SANDBOX
        );

        $this->setHttpClient($httpClient->withOptions($httpOptions->toArray()));
    }

    public static function getName(): string
    {
        return 'paypal';
    }

    public function setHttpClient(HttpClientInterface $httpClient): static
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Calls the PayPal API to generate an OAuth2 token
     * @return array The API response body
     */
    public function generateAuthToken(): array
    {
        $response = $this->httpClient->request(Request::METHOD_POST, '/v1/oauth2/token', [
            'auth_basic' => [$this->paypalClientId, $this->paypalClientSecret],
            'body' => 'grant_type=client_credentials'
        ]);

        return $response->toArray();
    }

    /**
     * Retrieves the PayPal OAuth2 token data from cache (if available) or generates a new one
     * @return array The OAuth2 token data
     */
    public function getAuthToken(): array
    {
        return $this->cache->get($this->getName(), function (ItemInterface $item): array {
            $tokenData = $this->generateAuthToken();

            $item->expiresAfter($tokenData['expires_in']);

            return $tokenData;
        });
    }

    public function sendData(GatewayCheckout $checkout): GatewayCheckout
    {
        return $checkout;
    }

    public function handleRedirect(Request $request): RedirectResponse
    {
        return new RedirectResponse('');
    }

    public function handleWebhook(Request $request): Response
    {
        return new Response();
    }
}
