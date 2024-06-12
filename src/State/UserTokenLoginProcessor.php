<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\UserTokenLoginDto;
use App\Entity\UserToken;
use App\Repository\UserRepository;
use App\Service\Auth\AuthService;
use App\Service\Auth\AuthTokenType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTokenLoginProcessor implements ProcessorInterface
{
    public function __construct(
        private AuthService $authService,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    /**
     * @param UserTokenLoginDto $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserToken
    {
        $user = $this->userRepository->findOneByIdentifier($data->identifier);

        if (!$user) {
            throw new HttpException(Response::HTTP_NOT_FOUND, sprintf("The user '%s' does not exist.", $data->identifier));
        }

        if (!$this->userPasswordHasher->isPasswordValid($user, $data->password)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, sprintf('The password could not be validated'));
        }

        $token = $this->authService->generateUserToken($user, AuthTokenType::Personal);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }
}
