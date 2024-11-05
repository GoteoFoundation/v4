<?php

namespace App\Gateway;

enum LinkType: string
{
    /**
     * A 'debug' type link indicates a link that is helpful to developers and platform maintainers to get info about the checkout.
     */
    case Debug = 'debug';

    /**
     * A 'payment' type link indicates a link where the checkout is available to an end user for payment.
     */
    case Payment = 'payment';
}
