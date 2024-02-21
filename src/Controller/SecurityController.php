<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\SessionUnavailableException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('v4/auth')]
class SecurityController extends AbstractController
{
    public function __construct(private IriConverterInterface $iriConverter)
    {
    }

    #[Route('/login', name: 'app_security_login', methods: ['POST'])]
    public function loginAction(#[CurrentUser()] $user = null): Response
    {
        if (!$user) {
            throw new SessionUnavailableException();
        }

        return new Response(null, Response::HTTP_NO_CONTENT, [
            'Location' => $this->iriConverter->getIriFromResource($user)
        ]);
    }

    #[Route('/logout', name: 'app_security_logout')]
    public function logoutAction(): void
    {
        // This Exception should never be reached unless
        // the firewall in security.firewalls.main.logout was not hit
        throw new \Exception();
    }
}
