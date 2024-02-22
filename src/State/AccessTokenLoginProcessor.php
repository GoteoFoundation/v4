<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\AccessTokenLoginDto;
use App\Entity\AccessToken;
use App\Repository\UserRepository;
use App\Service\Auth\AccessTokenType;
use App\Service\Auth\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class AccessTokenLoginProcessor implements ProcessorInterface
{
    public function __construct(
        private AuthService $authService,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AccessToken
    {
        /** @var AccessTokenLoginDto */
        $data;

        $user = $this->userRepository->findOneBy(['username' => $data->username]);

        if (!$user) {
            throw new UserNotFoundException();
        }

        if (!$this->userPasswordHasher->isPasswordValid($user, $data->password)) {
            throw new BadCredentialsException();
        }

        $token = $this->authService->generateAccessToken($user, AccessTokenType::Personal);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }
}
