<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Interface\AccountingOwnerInterface;
use App\Entity\Interface\UserOwnedInterface;
use AutoMapper\AutoMapperInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ApiResourceStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: RemoveProcessor::class)]
        private ProcessorInterface $removeProcessor,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private AutoMapperInterface $autoMapper,
        private Security $security,
    ) {}

    /**
     * @return mixed
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var Options */
        $stateOptions = $operation->getStateOptions();
        $entityClass = $stateOptions->getEntityClass();

        $target = new $entityClass();

        /** @var object */
        $entity = $this->autoMapper->map($data, $target, $this->buildMappingContext($data, $target));

        if ($operation instanceof DeleteOperationInterface) {
            $this->removeProcessor->process($entity, $operation, $uriVariables, $context);

            return null;
        }

        if ($entity->getId() === null && $entity instanceof UserOwnedInterface) {
            $entity->setOwner($this->security->getUser());
        }

        $this->persistProcessor->process($entity, $operation, $uriVariables, $context);
        $data->id = $entity->getId();

        return $data;
    }

    private function buildMappingContext(object $source, object $target): array
    {
        $ignoredAttributes = [];

        if (!isset($source->id)) {
            \array_push($ignoredAttributes, 'id');

            if ($target instanceof UserOwnedInterface && !isset($source->owner)) {
                \array_push($ignoredAttributes, 'owner');
            }

            if ($target instanceof AccountingOwnerInterface && !isset($source->accounting)) {
                \array_push($ignoredAttributes, 'accounting');
            }
        }

        return [
            'ignored_attributes' => $ignoredAttributes,
        ];
    }
}
