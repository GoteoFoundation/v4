<?php

namespace App\State\Accounting;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Accounting\AccountingApiResource;
use App\Entity\Accounting\Accounting;
use App\Mapping\AutoMapper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AccountingStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private AutoMapper $autoMapper,
    ) {}

    /**
     * @param AccountingApiResource
     *
     * @return Accounting
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var Accounting */
        $entity = $this->autoMapper->map($data, Accounting::class);
        $entity = $this->persistProcessor->process($entity, $operation, $uriVariables, $context);

        return $this->autoMapper->map($entity, AccountingApiResource::class);
    }
}
