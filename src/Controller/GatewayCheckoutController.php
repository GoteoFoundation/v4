<?php

namespace App\Controller;

use App\Entity\GatewayCheckout;
use App\Library\Economy\Payment\GatewayLocator;
use App\Repository\GatewayCheckoutRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/v4/gateway_checkouts')]
class GatewayCheckoutController extends AbstractController
{
    public function __construct(
        private GatewayLocator $gatewayLocator,
        private GatewayCheckoutRepository $gatewayCheckoutRepository
    ) {
    }

    private function getGatewayCheckout(int $id): GatewayCheckout
    {
        $gatewayCheckout = $this->gatewayCheckoutRepository->find($id);

        if (!$gatewayCheckout) {
            throw new NotFoundHttpException();
        }

        return $gatewayCheckout;
    }

    #[Route('/{id}/success', name: 'app_gateway_checkout_success')]
    public function success(int $id): Response
    {
        $gatewayCheckout = $this->getGatewayCheckout($id);
        $gatewayCheckout = $this->gatewayLocator
            ->getGatewayByCheckout($gatewayCheckout)
            ->onSuccess($gatewayCheckout);

        return new RedirectResponse($gatewayCheckout->getSuccessUrl());
    }

    #[Route('/{id}/failure', name: 'app_gateway_checkout_failure')]
    public function failure(int $id): Response
    {
        $gatewayCheckout = $this->getGatewayCheckout($id);
        $gatewayCheckout = $this->gatewayLocator
            ->getGatewayByCheckout($gatewayCheckout)
            ->onFailure($gatewayCheckout);

        return new RedirectResponse($gatewayCheckout->getFailureUrl());
    }
}
