<?php

namespace App\Gateway\Wallet;

use App\Entity\Accounting\Accounting;
use App\Entity\Accounting\Transaction;
use App\Entity\Money;
use App\Entity\WalletStatement;
use App\Library\Economy\MoneyService;
use App\Repository\WalletStatementRepository;
use Brick\Money as Brick;
use Doctrine\ORM\EntityManagerInterface;

class WalletService
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
                case StatementDirection::Incoming:
                    $total = $total->plus($transacted);
                    break;
                case StatementDirection::Outgoing:
                    $total = $total->minus($transacted);
                    break;
            }
        }

        return $this->money->toMoney($total);
    }

    /**
     * Puts the money of a Transaction into the target wallet.
     *
     * @param Transaction $transaction The Transaction targetting a wallet
     *
     * @return WalletStatement An incoming statement for the target wallet
     */
    public function save(Transaction $transaction): WalletStatement
    {
        $statement = new WalletStatement();
        $statement->setTransaction($transaction);
        $statement->setDirection(StatementDirection::Incoming);
        $statement->setBalance($transaction->getMoney());

        $this->entityManager->persist($statement);
        $this->entityManager->flush();

        return $statement;
    }

    /**
     * Takes the money of a Transaction from the origin wallet.
     *
     * @param Transaction $transaction The Transaction originating from a wallet
     *
     * @return WalletStatement An outgoing statement financed by previous incoming statements
     */
    public function spend(Transaction $transaction): WalletStatement
    {
        $origin = $transaction->getOrigin();

        $spendGoal = $transaction->getMoney();
        $spentTotal = new Money(0, $spendGoal->currency);

        $outgoing = new WalletStatement();
        $outgoing->setTransaction($transaction);
        $outgoing->setDirection(StatementDirection::Outgoing);

        $incomings = $this->getStatements($origin);
        foreach ($incomings as $incoming) {
            if ($spendGoal->amount === 0) {
                break;
            }

            $balance = $incoming->getBalance();

            if ($balance->amount === 0) {
                continue;
            }

            if ($this->money->isLess($spendGoal, $balance)) {
                $balanceSpent = $spendGoal;
            } else {
                $balanceSpent = $balance;
            }

            $spendGoal = $this->money->substract($balanceSpent, $spendGoal);
            $spentTotal = $this->money->add($balanceSpent, $spentTotal);

            $balance = $this->money->substract($balanceSpent, $balance);
            $incoming->setBalance($balance);

            $outgoing->setBalance($spentTotal);
            $outgoing->addFinancedBy($incoming);

            $this->entityManager->persist($incoming);
            $this->entityManager->persist($outgoing);
            $this->entityManager->flush();
        }

        return $outgoing;
    }
}
