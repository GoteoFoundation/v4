<?php

namespace App\Library\Economy\Payment;

use App\Entity\GatewayCheckout;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gateway interfaces are in charge of connecting with payment gateways to send them the data in `GatewayCheckout` instances.\
 * \
 * The flow is:
 * 1. A client app creates a `GatewayCheckout` record
 * 2. `App\State\GatewayCheckoutProcessor` calls the `sendData` method of the respective interface for the new `GatewayCheckout`
 * 3. The interface connects with the gateway and sends the data for the `GatewayCheckout`
 * 4. The interface updates the `GatewayCheckout` with the checkout url and reference given by the gateway for the checkout session
 * 5. `App\State\GatewayCheckoutProcessor` returns the response data to the client app
 * 6. The client app redirects the user to the payment with the gateway
 * 7. The gateway processes the payment and redirects the user back to v4
 * 8. `App\Controller\GatewayController` catches the redirection and calls the `handleRedirect` method of the respective interface
 * 9. `App\Controller\GatewayController` also catches webhook events sent by the gateway and calls the `handleWebhook` method 
 */
interface GatewayInterface
{
    /**
     * @return string A short, unique, descriptive string for this Gateway
     */
    public static function getName(): string;

    /**
     * Connects with the payment gateway and creates a checkout session so the gateway can process the payment.
     *
     * @param GatewayCheckout $checkout The GatewayCheckout with the data for the payment to be charged
     * @return GatewayCheckout The GatewayCheckout updated with the data given by the gateway
     */
    public function sendData(GatewayCheckout $checkout): GatewayCheckout;

    /**
     * When a user is redirected by the gateway we must handle the redirection
     * and then redirect the user back to a GUI.\
     * \
     * IMPORTANT: Redirection shouldn't be the only source of truth for gateway flow completion. 
     * Webhook handling is the preferred method.\
     * This should, ideally, only handle redirections of the user back to the desired web app.
     *
     * @param Request $request The HTTP Request object
     * @return Response A RedirectResponse object to where the user should be redirected
     */
    public function handleRedirect(Request $request): RedirectResponse;

    /**
     * When supported by the gateway, webhook events should be the preferred method to receive gateway flow updates.
     * 
     * @param Request $request The HTTP Request object of the webhook event
     * @return Response A Response object to send back to the gateway
     */
    public function handleWebhook(Request $request): Response;
}
