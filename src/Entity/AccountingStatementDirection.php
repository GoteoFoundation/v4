<?php

namespace App\Entity;

enum AccountingStatementDirection: string
{
    case Incoming = 'incoming';

    case Outgoing = 'outgoing';
}
