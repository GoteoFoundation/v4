<?php

namespace App\Service;

use App\Entity\AccountingFunding;
use App\Entity\AccountingIncoming;
use App\Entity\AccountingOutgoing;
use App\Entity\Transaction;
use App\Entity\TransactionExchange;
use App\Library\Economy\Currency\ExchangeLocator;

class AccountingService
{
    public function __construct(
        private ExchangeLocator $exchanges
    ) {
    }

    public function spendTransaction(Transaction $transaction): Transaction
    {
        $attempt = clone $transaction;
        $transactionExchange = null;

        $accounting = $attempt->getOrigin()->getAccounting();
        if (!$accounting->hasCurrencyOf($attempt)) {
            $exchange = $this->exchanges->getExchangeFor($attempt->getCurrency(), $accounting->getCurrency());
            $conversion = $exchange->getConversion($attempt, $accounting->getCurrency());

            $attempt->setAmount($conversion->getAmount());
            $attempt->setCurrency($conversion->getCurrency());
            
            $transactionExchange = new TransactionExchange(
                $transaction->getAmount(),
                $transaction->getCurrency(),
                $exchange->getConversionRate($transaction->getCurrency(), $accounting->getCurrency()),
                $exchange->getId()
            );
        }

        if ($accounting->isLessThan($attempt)) {
            throw new \Exception("The origin Accounting cannot secure the funding for the Transaction");
        }

        $outgoing = new AccountingOutgoing();
        $outgoing->setCurrency($accounting->getCurrency());
        $outgoing->setAccounting($accounting);
        $outgoing->setTransaction($transaction);
        $outgoing->setTransactionExchange($transactionExchange);

        foreach ($accounting->getIncomings() as $incoming) {
            if ($attempt->isZero()) break;
            if ($incoming->isZero()) continue;

            $funding = new AccountingFunding();
            $funding->setCurrency($incoming->getCurrency());

            if ($incoming->isGreaterThan($attempt)) {
                $funding->plus($attempt);
                $incoming->minus($attempt);
                $attempt->setAmount(0);
            }

            if ($incoming->isLessThanOrEqualTo($attempt)) {
                $funding->plus($incoming);
                $attempt->minus($incoming);
                $incoming->setAmount(0);
            }

            $incoming->addSpentOn($funding);

            $outgoing->plus($funding);
            $outgoing->addFinancedBy($funding);

            $accounting->minus($funding);

            continue;
        }

        $accounting->addOutgoing($outgoing);

        $origin = $transaction->getOrigin();
        $origin->setOutgoing($outgoing);

        $transaction->setOrigin($origin);

        return $transaction;
    }

    public function storeTransaction(Transaction $transaction): Transaction
    {
        $transactionExchange = null;

        $accounting = $transaction->getTarget()->getAccounting();
        if (!$accounting->hasCurrencyOf($transaction)) {
            $exchange = $this->exchanges->getExchangeFor($transaction->getCurrency(), $accounting->getCurrency());
            $conversion = $exchange->getConversion($transaction, $accounting->getCurrency());

            $transaction->setAmount($conversion->getAmount());
            $transaction->setCurrency($conversion->getCurrency());
            
            $transactionExchange = new TransactionExchange(
                $transaction->getAmount(),
                $transaction->getCurrency(),
                $exchange->getConversionRate($transaction->getCurrency(), $accounting->getCurrency()),
                $exchange->getId()
            );
        }

        $incoming = new AccountingIncoming();
        $incoming->setAmount($transaction->getAmount());
        $incoming->setAmountOriginal($transaction->getAmount());
        $incoming->setCurrency($accounting->getCurrency());
        $incoming->setAccounting($accounting);
        $incoming->setTransaction($transaction);
        $incoming->setTransactionExchange($transactionExchange);

        $accounting->setAmount($accounting->plus($incoming)->getAmount());
        $accounting->addIncoming($incoming);

        $target = $transaction->getTarget();
        $target->setIncoming($incoming);
    
        $transaction->setTarget($target);

        return $transaction;
    }
}
