<?php

namespace App\Library\Economy\Payment;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\GatewayCharge;
use App\Entity\GatewayCheckout;
use App\Entity\GatewayCheckoutStatus;
use App\Entity\GatewayLink;
use App\Entity\GatewayLinkType;
use App\Entity\GatewayTracking;
use App\Repository\GatewayCheckoutRepository;
use App\Service\GatewayCheckoutService;
use Brick\Money\Money as BrickMoney;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

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

    /** @see https://developer.paypal.com/api/rest/webhooks/event-names/#orders */
    public const PAYPAL_EVENT_ORDER_COMPLETED = 'CHECKOUT.ORDER.COMPLETED';

    /**
     * @see https://developer.paypal.com/docs/api/orders/v2/
     */
    private const PAYPAL_ORDER_INTENT = 'CAPTURE';

    public const TRACKING_TITLE_ORDER = 'PayPal Order ID';
    public const TRACKING_TITLE_TRANSACTION = 'PayPal Transaction ID';

    public function __construct(
        private PaypalGatewayService $paypal,
        private RouterInterface $router,
        private EntityManagerInterface $entityManager,
        private IriConverterInterface $iriConverter,
        private GatewayCheckoutService $checkoutService,
        private GatewayCheckoutRepository $checkoutRepository,
    ) {}

    public static function getName(): string
    {
        return 'paypal';
    }

    public function process(GatewayCheckout $checkout): GatewayCheckout
    {
        $order = $this->paypal->postOrder([
            'intent' => self::PAYPAL_ORDER_INTENT,
            'purchase_units' => $this->getPaypalPurchaseUnits($checkout),
            'payment_source' => $this->getPaypalPaymentSource($checkout),
        ]);

        $tracking = new GatewayTracking();
        $tracking->setValue($order['id']);
        $tracking->setTitle(self::TRACKING_TITLE_ORDER);

        $checkout->addGatewayTracking($tracking);

        foreach ($order['links'] as $linkData) {
            $linkType = \in_array($linkData['rel'], ['approve', 'payer-action'])
                ? GatewayLinkType::Payment
                : GatewayLinkType::Debug;

            $link = new GatewayLink();
            $link->setHref($linkData['href']);
            $link->setRel($linkData['rel']);
            $link->setMethod($linkData['method']);
            $link->setType($linkType);

            $checkout->addGatewayLink($link);
        }

        return $checkout;
    }

    public function handleRedirect(Request $request): RedirectResponse
    {
        if ($request->query->get('type') !== GatewayCheckoutService::RESPONSE_TYPE_SUCCESS) {
            throw new \Exception(sprintf('Checkout was not completed successfully.'));
        }

        $checkoutId = $request->query->get('checkoutId');

        $checkout = $this->checkoutRepository->find($checkoutId);
        if ($checkout === null) {
            throw new \Exception(sprintf("GatewayCheckout '%s' could not be found.", $checkoutId));
        }

        if ($checkout->getStatus() === GatewayCheckoutStatus::Charged) {
            return new RedirectResponse($this->iriConverter->getIriFromResource($checkout));
        }

        $orderId = $request->query->get('token');
        if (!$orderId) {
            throw new \Exception(sprintf('PayPal checkout order ID not provided by the gateway.'));
        }

        $order = $this->paypal->getOrder($orderId);
        if ($order['status'] !== self::PAYPAL_STATUS_APPROVED) {
            throw new \Exception(sprintf("PayPal checkout '%s' has not yet been processed successfully by the gateway.", $orderId));
        }

        $capture = $this->paypal->captureOrderPayment($order);
        if ($capture['status'] !== self::PAYPAL_STATUS_COMPLETED) {
            throw new \Exception(sprintf("Payment capture for PayPal checkout '%s' was not completed.", $orderId));
        }

        foreach ($capture['purchase_units'] as $purchaseUnit) {
            $tracking = new GatewayTracking();
            $tracking->setValue($purchaseUnit['payments']['captures'][0]['id']);
            $tracking->setTitle(self::TRACKING_TITLE_TRANSACTION);

            $checkout->addGatewayTracking($tracking);
        }

        $checkout = $this->checkoutService->chargeCheckout($checkout);

        // TO-DO: This should redirect the user to a GUI
        return new RedirectResponse($this->iriConverter->getIriFromResource($checkout));
    }

    public function handleWebhook(Request $request): Response
    {
        try {
            $event = $this->paypal->verifyWebhook($request);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'ERROR',
                'message' => $e->getMessage(),
                'requestBody' => $request->getContent(),
                'requestHeaders' => $request->headers->all(),
            ], Response::HTTP_ACCEPTED);
        }

        switch ($event['event_type']) {
            case self::PAYPAL_EVENT_ORDER_COMPLETED:
                return $this->handleOrderCompleted($event);
            default:
                return new Response('Event not supported', Response::HTTP_ACCEPTED);
        }

        return new Response();
    }

    private function handleOrderCompleted(array $event)
    {
        $orderId = $event['resource']['id'];

        $checkout = $this->checkoutRepository->findOneByTracking(self::TRACKING_TITLE_ORDER, $orderId);

        if ($checkout === null) {
            throw new \Exception(sprintf("Could not find any GatewayCheckout by the GatewayTracking '%s'", $orderId), 1);
        }

        foreach ($event['resource']['purchase_units'] as $purchaseUnit) {
            $tracking = new GatewayTracking();
            $tracking->setValue($purchaseUnit['payments']['captures'][0]['id']);
            $tracking->setTitle(self::TRACKING_TITLE_TRANSACTION);

            $checkout->addGatewayTracking($tracking);
        }

        $checkout = $this->checkoutService->chargeCheckout($checkout);
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

    private function getPaypalPurchaseUnits(GatewayCheckout $checkout): array
    {
        $units = [];

        foreach ($checkout->getCharges() as $charge) {
            $money = $this->getPaypalMoney($charge);
            $reference = $this->checkoutService->getGatewayReference($checkout, $charge);

            $units[] = [
                'reference_id' => $reference,
                'custom_id' => $reference,
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

    private function getPaypalPaymentSource(GatewayCheckout $checkout): array
    {
        return [
            'paypal' => [
                'experience_context' => [
                    'return_url' => $this->checkoutService->generateRedirectUrl($checkout),
                ],
            ],
        ];
    }
}
