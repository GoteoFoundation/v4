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
use App\Repository\User\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProjectStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: RemoveProcessor::class)]
        private ProcessorInterface $deleteProcessor,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private AutoMapper $autoMapper,
        private Security $security,
        private UserRepository $userRepository,
    ) {}

    /**
     * @param ProjectApiResource $data
     * 
     * @return ProjectApiResource|null
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var Project */
        $project = $this->autoMapper->map($data, Project::class, ['skip_null_values' => true]);

        if (!isset($data->id)) {
            $user = $this->security->getUser();
            $owner = $this->userRepository->findOneBy(['username' => $user->getUserIdentifier()]);

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
