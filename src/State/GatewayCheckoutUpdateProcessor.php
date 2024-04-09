<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\GatewayCheckoutUpdateDto;
use App\Entity\GatewayCheckout;
use App\Repository\GatewayCheckoutRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GatewayCheckoutUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private GatewayCheckoutRepository $checkoutRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param GatewayCheckoutUpdateDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): GatewayCheckout
    {
        $checkout = $this->checkoutRepository->find($uriVariables['id']);

        if (!$checkout) {
            throw new HttpException(Response::HTTP_NOT_FOUND, sprintf("The checkout '%s' does not exist.", $uriVariables['id']));
        }

        $checkout->setGatewayReference($data->gatewayReference);

        $this->entityManager->persist($checkout);
        $this->entityManager->flush();

        return $checkout;
    }
}
