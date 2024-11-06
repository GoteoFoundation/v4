<?php

namespace App\State\Accounting;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Accounting as ApiResource;
use App\Entity\Accounting as Entity;
use App\Mapping\Accounting\AccountingMapper;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AccountingStateProcessor implements ProcessorInterface
{
    public function __construct(
        private AccountingMapper $accountingMapper,
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
    ) {}

    /**
     * @param ApiResource\Accounting $data
     *
     * @return ApiResource\Accounting
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var Entity\Accounting */
        $entity = $this->accountingMapper->toEntity($data);

        $this->persistProcessor->process($entity, $operation, $uriVariables, $context);

        return $this->accountingMapper->toResource($entity);
    }
}
