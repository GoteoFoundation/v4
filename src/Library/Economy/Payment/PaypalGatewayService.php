<?php

namespace App\Library\Economy\Payment;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaypalGatewayService
{
    private CacheInterface $cache;

    public function __construct(
        private string $paypalApiAddress,
        private string $paypalClientId,
        private string $paypalClientSecret,
        private string $paypalWebhookId,
        private HttpClientInterface $httpClient,
    ) {
        $this->cache = new FilesystemAdapter();

        $httpOptions = new HttpOptions();
        $httpOptions->setBaseUri($paypalApiAddress);

        $this->setHttpClient($httpClient->withOptions($httpOptions->toArray()));
    }

    public function setHttpClient(HttpClientInterface $httpClient): static
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Calls the PayPal API to generate an OAuth2 token.
     *
     * @return array The API response body
     */
    public function generateAuthToken(): array
    {
        $response = $this->httpClient->request(Request::METHOD_POST, '/v1/oauth2/token', [
            'auth_basic' => [$this->paypalClientId, $this->paypalClientSecret],
            'body' => 'grant_type=client_credentials',
        ]);

        return $response->toArray();
    }

    /**
     * Retrieves the PayPal OAuth2 token data from cache (if available) or generates a new one.
     *
     * @return array The OAuth2 token data
     */
    public function getAuthToken(): array
    {
        return $this->cache->get($this->paypalApiAddress, function (ItemInterface $item): array {
            $tokenData = $this->generateAuthToken();

            $item->expiresAfter($tokenData['expires_in']);

            return $tokenData;
        });
    }

    /**
     * Creates a PayPal Order resource with the given data.
     *
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_create
     */
    public function postOrder(array $order): array
    {
        $response = $this->httpClient->request(Request::METHOD_POST, '/v2/checkout/orders', [
            'auth_bearer' => $this->getAuthToken()['access_token'],
            'json' => $order,
        ]);

        $content = \json_decode($response->getContent(), true);
        if (!\in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            throw new \Exception($content['message']);
        }

        return $content;
    }

    /**
     * Retrieve a PayPal Order resource.
     *
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_get
     */
    public function getOrder(string $orderId): array
    {
        $request = $this->httpClient->request(
            Request::METHOD_GET,
            sprintf('/v2/checkout/orders/%s', $orderId),
            [
                'auth_bearer' => $this->getAuthToken()['access_token'],
            ]
        );

        if ($request->getStatusCode() !== Response::HTTP_OK) {
            throw new \Exception(sprintf("PayPal checkout '%s' could not be requested.", $orderId));
        }

        return \json_decode($request->getContent(), true);
    }

    /**
     * Process post user-approval payment capture for a given Order.
     *
     * @see https://developer.paypal.com/docs/api/orders/v2/#orders_capture
     */
    public function captureOrderPayment(array $order): array
    {
        $link = \array_filter($order['links'], function ($order) {
            return $order['rel'] === 'capture';
        });

        if (empty($link)) {
            throw new \Exception(sprintf("PayPal checkout '%s' was not ready for capture.", $order['id']));
        }

        $link = \array_pop($link);
        $request = $this->httpClient->request(
            $link['method'],
            $link['href'],
            [
                'auth_bearer' => $this->getAuthToken()['access_token'],
                'headers' => ['Content-Type' => 'application/json'],
            ]
        );

        if ($request->getStatusCode() !== Response::HTTP_CREATED) {
            throw new \Exception(sprintf("Payment capture for PayPal checkout '%s' was unsuccessful.", $order['id']));
        }

        return \json_decode($request->getContent(), true);
    }

    /**
     * Verifies a PayPal webhook request.
     *
     * @param Request $request The webhook request
     *
     * @return array The webhook event data
     *
     * @throws \Exception If the verification fails
     */
    public function verifyWebhook(Request $request): array
    {
        $headers = $request->headers;

        $isSignatureValid = \openssl_verify(
            implode('|', [
                $headers->get('paypal-transmission-id'),
                $headers->get('paypal-transmission-type'),
                $this->paypalWebhookId,
                \crc32($request->getContent()),
            ]),
            \base64_decode($headers->get('paypal-transmission-sig')),
            \openssl_pkey_get_public(\file_get_contents($headers->get('paypal-cert-url')))
        ) === 1;

        if (!$isSignatureValid) {
            throw new \Exception('Could not verify PayPal webhook signature.');
        }

        return \json_decode($request->getContent(), true);
    }
}
