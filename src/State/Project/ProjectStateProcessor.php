<?php

namespace App\State\Project;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Project\ProjectApiResource;
use App\Entity\Project\Project;
use App\Mapping\AutoMapper;
use App\Service\Auth\AuthService;
use App\State\EntityStateProcessor;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ProjectStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityStateProcessor $entityStateProcessor,
        private AutoMapper $autoMapper,
        private AuthService $authService,
    ) {}

    /**
     * @param ProjectApiResource $data
     *
     * @return ProjectApiResource|null
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var Project */
        $project = $this->autoMapper->map($data, Project::class);

        if (!$project->getId()) {
            $owner = $this->authService->getUser();

            if (!$owner) {
                throw new AuthenticationException();
            }

            $project->setOwner($owner);
        }

        $project = $this->entityStateProcessor->process($project, $operation, $uriVariables, $context);

        if ($project === null) {
            return null;
        }

        return $this->autoMapper->map($project, $data);
    }
}
