<?php

namespace App\Library\Economy\Payment;

use App\Entity\GatewayChargeType;
use App\Entity\GatewayCheckout;
use App\Entity\GatewayCheckoutStatus;
use App\Repository\GatewayCheckoutRepository;
use Stripe\Checkout\Session as StripeSession;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class StripeGateway implements GatewayInterface
{
    private StripeClient $stripe;

    public function __construct(
        private string $stripeApiKey,
        private RouterInterface $router,
        private GatewayCheckoutRepository $gatewayCheckoutRepository
    ) {
        $this->stripe = new StripeClient($stripeApiKey);
    }

    public static function getName(): string
    {
        return 'stripe';
    }

    public function create(GatewayCheckout $checkout): GatewayCheckout
    {
        $redirect = $this->router->generate(
            'gateway_redirect',
            [
                'type' => 'success',
                'gateway' => $this->getName(),
            ],
            RouterInterface::ABSOLUTE_URL
        );

        $session = $this->stripe->checkout->sessions->create([
            'customer_email' => $checkout->getOrigin()->getUser()->getEmail(),
            'mode' => $this->getStripeMode($checkout),
            'line_items' => $this->getStripeLineItems($checkout),
            # Because Symfony's Router encodes query parameters, the value {CHECKOUT_SESSION_ID}
            # is not properly sent to Stripe and the redirection fails,
            # that's why we add the session_id template variable like this.
            # https://docs.stripe.com/payments/checkout/custom-success-page?lang=php#modify-the-success-url
            'success_url' => sprintf("%s&session_id={CHECKOUT_SESSION_ID}", $redirect),
        ]);

        $checkout->setCheckoutUrl($session->url);
        $checkout->setGatewayReference($session->id);

        return $checkout;
    }

    public function handleRedirect(Request $request): GatewayCheckout
    {
        $checkout = $this->gatewayCheckoutRepository->findOneBy(
            ['gatewayReference' => $request->query->get('session_id')]
        );

        $checkout->setStatus(GatewayCheckoutStatus::Charged);

        return $checkout;
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
                    'statement_descriptor' => $charge::MESSAGE_STATEMENT
                ]
            ];

            if ($charge->getType() === GatewayChargeType::Recurring) {
                $price['recurring'] = ['interval' => 'month'];
            }

            $items[] = [
                'quantity' => 1,
                'price' => $this->stripe->prices->create($price)->id
            ];
        }

        return $items;
    }
}
