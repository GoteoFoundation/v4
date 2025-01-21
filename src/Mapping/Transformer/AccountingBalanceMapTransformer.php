<?php

namespace App\Mapping\Transformer;

use App\Entity\Accounting\Accounting;
use App\Service\AccountingService;
use AutoMapper\Transformer\PropertyTransformer\PropertyTransformerInterface;

class AccountingBalanceMapTransformer implements PropertyTransformerInterface
{
    public function __construct(
        private AccountingService $accountingService,
    ) {}

    /**
     * @param Accounting $source
     */
    public function transform(mixed $value, object|array $source, array $context): mixed
    {
        return $this->accountingService->calcBalance($source);
    }
}
