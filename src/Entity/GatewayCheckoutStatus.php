<?php

namespace App\Entity;

enum GatewayCheckoutStatus: string
{
    case Pending = 'pending';

    case Charged = 'charged';
}
