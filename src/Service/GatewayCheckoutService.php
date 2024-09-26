<?php

namespace App\Service;

use App\Controller\GatewaysController;
use Symfony\Component\Routing\RouterInterface;

class GatewayCheckoutService
{
    public const RESPONSE_TYPE_SUCCESS = 'success';
    public const RESPONSE_TYPE_FAILURE = 'failure';

    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function generateRedirectUrl(string $gateway, string $type = self::RESPONSE_TYPE_SUCCESS, array $parameters = []): string
    {
        return $this->router->generate(
            GatewaysController::REDIRECT,
            [
                'type' => $type,
                'gateway' => $gateway,
            ],
            RouterInterface::ABSOLUTE_URL
        );
    }
}
