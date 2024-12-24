<?php

namespace App\State\Project;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Project\RewardClaimApiResource;
use App\Entity\Project\RewardClaim;
use App\Mapping\AutoMapper;
use App\Service\Auth\AuthService;
use App\Service\Project\RewardService;
use App\State\EntityStateProcessor;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class RewardClaimStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityStateProcessor $entityStateProcessor,
        private AutoMapper $autoMapper,
        private AuthService $authService,
        private RewardService $rewardService,
    ) {}

    /**
     * @param RewardClaimApiResource $data
     *
     * @return RewardClaimApiResource|null
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var RewardClaim */
        $claim = $this->autoMapper->map($data, RewardClaim::class);

        if (!$claim->getId()) {
            $owner = $this->authService->getUser();

            if (!$owner) {
                throw new AuthenticationException();
            }

            $claim->setOwner($owner);
        }

        $claim = $this->rewardService->processClaim($claim);
        $claim = $this->entityStateProcessor->process($claim, $operation, $uriVariables, $context);

        return $this->autoMapper->map($claim, $data);
    }
}
