<?php

namespace App\State\Project;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Project\ProjectApiResource;
use App\Entity\Project\Project;
use App\Mapping\AutoMapper;
use App\Service\Auth\AuthService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ProjectStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: RemoveProcessor::class)]
        private ProcessorInterface $deleteProcessor,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
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

        if (!isset($data->id)) {
            $owner = $this->authService->getUser();

            if (!$owner) {
                throw new AuthenticationException();
            }

            $project->setOwner($owner);
        }

        if ($operation instanceof DeleteOperationInterface) {
            $this->deleteProcessor->process($project, $operation, $uriVariables, $context);

            return null;
        }

        $this->persistProcessor->process($project, $operation, $uriVariables, $context);

        return $this->autoMapper->map($project, $data);
    }
}
