<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\UserTokenLoginDto;
use App\Entity\UserToken;
use App\Repository\UserRepository;
use App\Service\Auth\AuthTokenType;
use App\Service\Auth\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

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
        $user = $this->userRepository->findOneBy(['username' => $data->username]);

        if (!$user) {
            throw new UserNotFoundException();
        }

        if (!$this->userPasswordHasher->isPasswordValid($user, $data->password)) {
            throw new BadCredentialsException();
        }

        $token = $this->authService->generateUserToken($user, AuthTokenType::Personal);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }
}
