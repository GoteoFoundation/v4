<?php

namespace App\Library\Economy\Payment;

use App\Entity\Transaction;

interface GatewayInterface
{
    /**
     * @return string A short, unique, descriptive string for this Gateway
     */
    public static function getName(): string;

    /**
     * When processing a Transaction a Gateway must validate it using it's own means.
     * 
     * Gateways are trusted to have secured the funds in the Transaction.
     * @param Transaction
     * @return Transaction
     */
    public function process(Transaction $transaction): Transaction;
}
