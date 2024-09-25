<?php

namespace App\Library\Economy\Payment;

use ApiPlatform\Api\IriConverterInterface;
use App\Controller\GatewaysController;
use App\Entity\AccountingTransaction;
use App\Entity\GatewayCharge;
use App\Entity\GatewayCheckout;
use App\Entity\GatewayCheckoutLink;
use App\Entity\GatewayCheckoutLinkType;
use App\Entity\GatewayCheckoutStatus;
use App\Repository\GatewayCheckoutRepository;
use Brick\Money\Money as BrickMoney;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @see https://developer.paypal.com/studio/checkout/standard/integrate
 */
class PaypalGateway implements GatewayInterface
{
    public const PAYPAL_API_ADDRESS_LIVE = 'https://api-m.paypal.com';
    public const PAYPAL_API_ADDRESS_SANDBOX = 'https://api-m.sandbox.paypal.com';

    /** @see https://developer.paypal.com/docs/api/orders/v2/#orders_get!c=200&path=status&t=response */
    public const PAYPAL_STATUS_APPROVED = 'APPROVED';
    public const PAYPAL_STATUS_COMPLETED = 'COMPLETED';

    /**
     * @see https://developer.paypal.com/docs/api/orders/v2/
     */
    private const PAYPAL_ORDER_INTENT = 'CAPTURE';

    private CacheInterface $cache;

    public function __construct(
        private string $appEnv,
        private string $paypalClientId,
        private string $paypalClientSecret,
        private RouterInterface $router,
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $entityManager,
        private IriConverterInterface $iriConverter,
        private GatewayCheckoutRepository $checkoutRepository,
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
        return $this->cache->get($this->getName(), function (ItemInterface $item): array {
            $tokenData = $this->generateAuthToken();

            $item->expiresAfter($tokenData['expires_in']);

            return $tokenData;
        });
    }

    public function sendData(GatewayCheckout $checkout): GatewayCheckout
    {
        $response = $this->httpClient->request(Request::METHOD_POST, '/v2/checkout/orders', [
            'auth_bearer' => $this->getAuthToken()['access_token'],
            'json' => [
                'intent' => self::PAYPAL_ORDER_INTENT,
                'purchase_units' => $this->getPaypalPurchaseUnits($checkout),
                'payment_source' => $this->getPaypalPaymentSource($checkout),
            ],
        ]);

        $content = json_decode($response->getContent(), true);

        if (!\in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_CREATED])) {
            throw new \Exception($content['message']);
        }

        $checkout->setGatewayReference($content['id']);

        foreach ($content['links'] as $linkData) {
            $linkType = \in_array($linkData['rel'], ['approve', 'payer-action'])
                ? GatewayCheckoutLinkType::Payment
                : GatewayCheckoutLinkType::Debug;

            $link = new GatewayCheckoutLink();
            $link->setHref($linkData['href']);
            $link->setRel($linkData['rel']);
            $link->setMethod($linkData['method']);
            $link->setType($linkType);

            $checkout->addLink($link);
        }

        return $checkout;
    }

    private function handleSuccess(GatewayCheckout $checkout): GatewayCheckout
    {
        $checkout->setStatus(GatewayCheckoutStatus::Charged);

        foreach ($checkout->getCharges() as $charge) {
            $transaction = new AccountingTransaction();
            $transaction->setMoney($charge->getMoney());
            $transaction->setOrigin($checkout->getOrigin());
            $transaction->setTarget($charge->getTarget());

            $this->entityManager->persist($transaction);

            $charge->setTransaction($transaction);

            $this->entityManager->persist($charge);
        }

        $this->entityManager->flush();

        return $checkout;
    }

    public function handleRedirect(Request $request): RedirectResponse
    {
        $token = $request->query->get('token');

        if ($request->query->get('type') !== self::RESPONSE_TYPE_SUCCESS) {
            throw new \Exception(sprintf("PayPal checkout '%s' was not completed successfully.", $token));
        }

        $requestUri = sprintf('/v2/checkout/orders/%s', $token);
        $request = $this->httpClient->request(Request::METHOD_GET, $requestUri, [
            'auth_bearer' => $this->getAuthToken()['access_token'],
        ]);

        if ($request->getStatusCode() !== Response::HTTP_OK) {
            throw new \Exception(sprintf("PayPal checkout '%s' could not be requested.", $token));
        }

        $session = \json_decode($request->getContent(), true);
        $checkout = $this->checkoutRepository->findOneBy(
            ['gatewayReference' => $token]
        );

        if ($checkout === null) {
            throw new \Exception(sprintf("PayPal checkout '%s' exists but no GatewayCheckout with that reference was found.", $token));
        }

        if ($checkout->getStatus() === GatewayCheckoutStatus::Charged) {
            return new RedirectResponse($this->iriConverter->getIriFromResource($checkout));
        }

        if (!\in_array($session['status'], [self::PAYPAL_STATUS_APPROVED, self::PAYPAL_STATUS_COMPLETED])) {
            throw new \Exception(sprintf("PayPal checkout '%s' has not yet been processed successfully by the gateway.", $token));
        }

        $checkout = $this->handleSuccess($checkout);

        // TO-DO: This should redirect the user to a GUI
        return new RedirectResponse($this->iriConverter->getIriFromResource($checkout));
    }

    public function handleWebhook(Request $request): Response
    {
        return new Response();
    }

    private function getPaypalMoney(GatewayCharge $charge): array
    {
        $brick = BrickMoney::ofMinor(
            $charge->getMoney()->amount,
            $charge->getMoney()->currency
        );

        return [
            'value' => $brick->getAmount()->__toString(),
            'currency_code' => $brick->getCurrency()->getCurrencyCode(),
        ];
    }

    private function getPaypalReference(GatewayCheckout $checkout, GatewayCharge $charge): string
    {
        return sprintf('CO%d-CH%d', $checkout->getId(), $charge->getId());
    }

    private function getPaypalPurchaseUnits(GatewayCheckout $checkout): array
    {
        $units = [];

        foreach ($checkout->getCharges() as $charge) {
            $money = $this->getPaypalMoney($charge);
            $reference = $this->getPaypalReference($checkout, $charge);

            $units[] = [
                'reference_id' => $reference,
                'items' => [
                    [
                        'name' => $charge::MESSAGE_STATEMENT,
                        'description' => $charge::MESSAGE_STATEMENT,
                        'quantity' => '1',
                        'unit_amount' => [
                            ...$money,
                        ],
                    ],
                ],
                'amount' => [
                    ...$money,
                    'breakdown' => [
                        'item_total' => [
                            ...$money,
                        ],
                    ],
                ],
            ];
        }

        return $units;
    }

    private function getPaypalPaymentSource(): array
    {
        $successUrl = $this->router->generate(
            GatewaysController::REDIRECT,
            [
                'type' => self::RESPONSE_TYPE_SUCCESS,
                'gateway' => $this->getName(),
            ],
            RouterInterface::ABSOLUTE_URL
        );

        return [
            'paypal' => [
                'experience_context' => [
                    'return_url' => $successUrl,
                ],
            ],
        ];
    }
}
