<?php

namespace App\Entity;

enum WalletStatementDirection: string
{
    /**
     * The Transaction was received by the Accounting.
     */
    case Incoming = 'incoming';

    /**
     * The Transaction was issued by the Accounting.
     */
    case Outgoing = 'outgoing';
}
