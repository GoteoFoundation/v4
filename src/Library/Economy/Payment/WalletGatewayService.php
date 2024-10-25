<?php

namespace App\Library\Economy\Payment;

use App\Entity\Accounting;
use App\Entity\AccountingTransaction;
use App\Entity\Money;
use App\Entity\WalletStatement;
use App\Entity\WalletStatementDirection;
use App\Library\Economy\MoneyService;
use App\Repository\WalletStatementRepository;
use Brick\Money as Brick;
use Doctrine\ORM\EntityManagerInterface;

class WalletGatewayService
{
    public function __construct(
        private MoneyService $money,
        private WalletStatementRepository $walletStatementRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @return \App\Entity\WalletStatement[]
     */
    public function getStatements(Accounting $accounting): array
    {
        return $this->walletStatementRepository->findByAccounting($accounting);
    }

    public function getBalance(Accounting $accounting): Money
    {
        $total = Brick\Money::ofMinor(0, $accounting->getCurrency());
        $statements = $this->getStatements($accounting);

        foreach ($statements as $statement) {
            $transacted = $this->money->toBrick($statement->getTransaction()->getMoney());

            switch ($statement->getDirection()) {
                case WalletStatementDirection::Incoming:
                    $total = $total->plus($transacted);
                    break;
                case WalletStatementDirection::Outgoing:
                    $total = $total->minus($transacted);
                    break;
            }
        }

        return $this->money->toMoney($total);
    }

    /**
     * Puts the money of a Transaction into the target wallet.
     *
     * @param AccountingTransaction $transaction The Transaction targetting a wallet
     *
     * @return WalletStatement An incoming statement for the target wallet
     */
    public function save(AccountingTransaction $transaction): WalletStatement
    {
        $statement = new WalletStatement();
        $statement->setTransaction($transaction);
        $statement->setDirection(WalletStatementDirection::Incoming);
        $statement->setBalance($transaction->getMoney());

        $this->entityManager->persist($statement);
        $this->entityManager->flush();

        return $statement;
    }

    /**
     * Takes the money of a Transaction from the origin wallet.
     *
     * @param AccountingTransaction $transaction The Transaction originating from a wallet
     *
     * @return WalletStatement An outgoing statement financed by previous incoming statements
     */
    public function spend(AccountingTransaction $transaction): WalletStatement
    {
        $origin = $transaction->getOrigin();
        $toSpend = $transaction->getMoney();

        $outgoing = new WalletStatement();
        $outgoing->setTransaction($transaction);
        $outgoing->setDirection(WalletStatementDirection::Outgoing);

        $incomings = $this->getStatements($origin);
        foreach ($incomings as $incoming) {
            $spent = new Money(0, $toSpend->currency);
            $balance = $incoming->getBalance();

            if ($balance->amount === 0) {
                continue;
            }

            if ($this->money->isMoreOrSame($balance, $spent)) {
                $spent = $toSpend;
            } else {
                $spent = $this->money->add($balance, $spent);
            }

            $balance = $this->money->substract($spent, $balance);

            $incoming->setBalance($balance);

            $outgoing->setBalance($spent);
            $outgoing->addFinancedBy($incoming);

            $this->entityManager->persist($incoming);
            $this->entityManager->persist($outgoing);
            $this->entityManager->flush();

            return $outgoing;
        }
    }
}
