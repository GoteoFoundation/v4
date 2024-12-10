<?php

namespace App\State\Project;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Project\ProjectApiResource;
use App\ApiResource\User\UserApiResource;
use App\Entity\Project\Project;
use App\Mapping\AutoMapper;
use App\Repository\User\UserRepository;
use App\State\ApiResourceStateProcessor;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectStateProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository,
        private AutoMapper $autoMapper,
        private ApiResourceStateProcessor $stateProcessor
    ) {}

    /**
     * @param ProjectApiResource $data
     * 
     * @return Project|null
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []) {
        if (!isset($data->id)) {
            $user = $this->security->getUser();
            $owner = $this->userRepository->findOneByIdentifier($user->getUserIdentifier());

            $data->owner = $this->autoMapper->map($owner, UserApiResource::class);
        }

        return $this->stateProcessor->process($data, $operation, $uriVariables, $context);
    }
}
