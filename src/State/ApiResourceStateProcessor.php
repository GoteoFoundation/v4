<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Mapping\AutoMapper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ApiResourceStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: RemoveProcessor::class)]
        private ProcessorInterface $deleteProcessor,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private AutoMapper $autoMapper,
    ) {}

    /**
     * @return T2
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $entity = $this->getEntity($data, $operation->getStateOptions());

        if ($operation instanceof DeleteOperationInterface) {
            $this->deleteProcessor->process($entity, $operation, $uriVariables, $context);

            return null;
        }

        $this->persistProcessor->process($entity, $operation, $uriVariables, $context);

        return $this->autoMapper->map($entity, $data);
    }

    public function getEntity(mixed $data, Options $options): object
    {
        /** @var object */
        $entity = $this->autoMapper->map($data, $options->getEntityClass());

        return $entity;
    }
}
