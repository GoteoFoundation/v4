<?php

namespace App\State\User;

use ApiPlatform\Metadata as API;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\User\UserApiResource;
use App\Dto\UserSignupDto;
use App\Entity\User\User;
use App\Mapping\AutoMapper;
use App\State\EntityStateProcessor;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserSignupProcessor implements ProcessorInterface
{
    public function __construct(
        private AutoMapper $autoMapper,
        private EntityStateProcessor $entityStateProcessor,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {}

    /**
     * @param UserSignupDto $data
     */
    public function process(mixed $data, API\Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var User */
        $user = $this->autoMapper->map($data, User::class);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $data->password));
        $user = $this->entityStateProcessor->process($user, $operation, $uriVariables, $context);

        return $this->autoMapper->map($user, UserApiResource::class);
    }
}
