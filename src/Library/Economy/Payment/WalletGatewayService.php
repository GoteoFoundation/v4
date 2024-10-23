<?php

namespace App\Library\Economy\Payment;

use App\Entity\Accounting;
use App\Entity\Money;
use App\Entity\WalletStatementDirection;
use App\Repository\WalletStatementRepository;
use Brick\Money as Brick;

class WalletGatewayService
{
    public function __construct(
        private WalletStatementRepository $walletStatementRepository,
    ) {}

    public function getAccountingBalance(Accounting $accounting): Money
    {
        $available = Brick\Money::ofMinor(0, $accounting->getCurrency());
        $statements = $this->walletStatementRepository->findByAccounting($accounting);

        foreach ($statements as $statement) {
            $transaction = $statement->getTransaction();
            $money = Brick\Money::ofMinor(
                $transaction->getMoney()->amount,
                $transaction->getMoney()->currency
            );

            switch ($statement->getDirection()) {
                case WalletStatementDirection::Incoming:
                    $available = $available->plus($money);
                    break;
                case WalletStatementDirection::Outgoing:
                    $available = $available->minus($money);
                    break;
            }
        }

        return new Money(
            $available->getMinorAmount()->toInt(),
            $available->getCurrency()->getCurrencyCode()
        );
    }
}
