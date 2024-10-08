<?php

namespace App\Library\Economy\Payment;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\GatewayChargeType;
use App\Entity\GatewayCheckout;
use App\Entity\GatewayCheckoutStatus;
use App\Entity\GatewayLink;
use App\Entity\GatewayLinkType;
use App\Entity\GatewayTracking;
use App\Repository\GatewayCheckoutRepository;
use App\Service\GatewayCheckoutService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\StripeClient;
use Stripe\Webhook as StripeWebhook;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class StripeGateway implements GatewayInterface
{
    public const TRACKING_TITLE_CHECKOUT = 'Stripe Checkout Session ID';

    private StripeClient $stripe;

    public function __construct(
        private string $stripeApiKey,
        private string $stripeWebhookSecret,
        private RouterInterface $router,
        private GatewayCheckoutService $checkoutService,
        private GatewayCheckoutRepository $checkoutRepository,
        private EntityManagerInterface $entityManager,
        private IriConverterInterface $iriConverter,
    ) {
        $this->stripe = new StripeClient($stripeApiKey);
    }

    public static function getName(): string
    {
        return 'stripe';
    }

    public function process(GatewayCheckout $checkout): GatewayCheckout
    {
        $session = $this->stripe->checkout->sessions->create([
            'customer_email' => $checkout->getOrigin()->getUser()->getEmail(),
            'mode' => $this->getStripeMode($checkout),
            'line_items' => $this->getStripeLineItems($checkout),
            // Because Symfony's Router encodes query parameters, the value {CHECKOUT_SESSION_ID}
            // is not properly sent to Stripe and the redirection fails,
            // that's why we add the session_id template variable like this.
            // https://docs.stripe.com/payments/checkout/custom-success-page?lang=php#modify-the-success-url
            'success_url' => sprintf('%s&session_id={CHECKOUT_SESSION_ID}', $this->checkoutService->generateRedirectUrl($checkout)),
        ]);

        $link = new GatewayLink();

        $link->setHref($session->url);
        $link->setRel('approve');
        $link->setMethod(Request::METHOD_GET);
        $link->setType(GatewayLinkType::Payment);

        $checkout->addGatewayLink($link);

        $tracking = new GatewayTracking();
        $tracking->setValue($session->id);
        $tracking->setTitle(self::TRACKING_TITLE_CHECKOUT);

        $checkout->addGatewayTracking($tracking);

        return $checkout;
    }

    public function handleRedirect(Request $request): RedirectResponse
    {
        $sessionId = $request->query->get('session_id');

        $session = $this->stripe->checkout->sessions->retrieve($sessionId);
        $checkout = $this->checkoutRepository->find($request->query->get('checkoutId'));

        if ($checkout === null) {
            throw new \Exception(sprintf("Stripe checkout '%s' exists but no GatewayCheckout with that reference was found.", $sessionId));
        }

        if ($checkout->getStatus() === GatewayCheckoutStatus::Charged) {
            return $checkout;
        }

        if ($request->query->get('type') !== GatewayCheckoutService::RESPONSE_TYPE_SUCCESS) {
            return $checkout;
        }

        if ($session->payment_status !== StripeSession::PAYMENT_STATUS_PAID) {
            return $checkout;
        }

        $checkout = $this->checkoutService->chargeCheckout($checkout);

        // TO-DO: This should redirect the user to a GUI
        return new RedirectResponse($this->iriConverter->getIriFromResource($checkout));
    }

    public function handleWebhook(Request $request): Response
    {
        $webhook = StripeWebhook::constructEvent(
            $request->getContent(),
            $request->headers->get('STRIPE_SIGNATURE'),
            $this->stripeWebhookSecret
        );

        switch ($webhook->type) {
            default:
                return new JsonResponse([
                    'error' => sprintf("The event '%s' is not supported", $webhook->type),
                ], Response::HTTP_BAD_REQUEST);
                break;
        }
    }

    private function getStripeMode(GatewayCheckout $checkout): string
    {
        foreach ($checkout->getCharges() as $charge) {
            if ($charge->getType() === GatewayChargeType::Recurring) {
                return StripeSession::MODE_SUBSCRIPTION;
            }
        }

        return StripeSession::MODE_PAYMENT;
    }

    private function getStripeLineItems(GatewayCheckout $checkout): array
    {
        $items = [];

        foreach ($checkout->getCharges() as $charge) {
            $price = [
                'currency' => $charge->getMoney()->currency,
                'unit_amount' => $charge->getMoney()->amount,
                'product_data' => [
                    'name' => $charge::MESSAGE_STATEMENT,
                    'statement_descriptor' => $charge::MESSAGE_STATEMENT,
                ],
            ];

            if ($charge->getType() === GatewayChargeType::Recurring) {
                $price['recurring'] = ['interval' => 'month'];
            }

            $items[] = [
                'quantity' => 1,
                'price' => $this->stripe->prices->create($price)->id,
            ];
        }

        return $items;
    }
}
