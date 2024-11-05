<?php

namespace App\Entity\Gateway;

enum CheckoutStatus: string
{
    case Pending = 'pending';

    case Charged = 'charged';
}
