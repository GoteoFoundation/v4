<?php

namespace App\State\User;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata as API;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $innerProcessor,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {}

    /**
     * @param UserApiResource $data
     * @return UserApiResource
     */
    public function process(mixed $data, API\Operation $operation, array $uriVariables = [], array $context = [])
    {
        return $data;
    }
}
