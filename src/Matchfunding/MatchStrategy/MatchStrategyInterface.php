<?php

namespace App\Matchfunding\MatchStrategy;

use App\Entity\Accounting\Transaction;

interface MatchStrategyInterface
{
    /**
     * A unique name for the strategy.
     */
    public static function getName(): string;

    /**
     * Process a Transaction targeting a Matchfunding that uses this strategy.
     *
     * @param Transaction $transaction The input Transaction that is eligible for matching
     *
     * @return Transaction A new output Transaction for the matching
     */
    public function match(Transaction $transaction): Transaction;
}
