<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use App\Library\Economy\Payment\GatewayLocator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v4/controllers')]
class GatewayController extends AbstractController
{
    public function __construct(
        private GatewayLocator $gatewayLocator,
        private EntityManagerInterface $entityManager,
        private IriConverterInterface $iriConverter
    ) {
    }

    #[Route('/gateway_redirect', name: 'gateway_redirect')]
    public function handleRedirect(Request $request): Response
    {
        $gateway = $this->gatewayLocator->getGateway($request->query->get('gateway'));
        $checkout = $gateway->handleRedirect($request);

        $this->entityManager->persist($checkout);
        $this->entityManager->flush();

        // TO-DO: This should redirect the user to a GUI
        return new RedirectResponse($this->iriConverter->getIriFromResource($checkout));
    }
}
