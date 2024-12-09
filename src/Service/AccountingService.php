<?php

namespace App\Service;

use App\Entity\Accounting\Accounting;
use App\Entity\Money;
use App\Entity\User\User;
use App\Gateway\Wallet\WalletService;
use App\Library\Economy\MoneyService;
use App\Repository\Accounting\TransactionRepository;

class AccountingService
{
    public function __construct(
        private MoneyService $money,
        private WalletService $wallet,
        private TransactionRepository $transactionRepository,
    ) {}

    public function calcBalance(Accounting $accounting): Money
    {
        $owner = $accounting->getOwner();

        if ($owner instanceof User) {
            return $this->wallet->getBalance($accounting);
        }

        $balance = new Money(0, $accounting->getCurrency());
        $transactions = $this->transactionRepository->findByAccounting($accounting);

        foreach ($transactions as $transaction) {
            if ($transaction->getTarget() === $accounting) {
                $balance = $this->money->add($transaction->getMoney(), $balance);
            }

            if ($transaction->getOrigin() === $accounting) {
                $balance = $this->money->substract($transaction->getMoney(), $balance);
            }
        }

        return $balance;
    }
}
