<?php

namespace App\Entity;

enum GatewayCheckoutLinkType: string
{
    /**
     * A 'platform' type link indicates a link that is to be consumed by the platform.
     */
    case Platform = 'platform';

    /**
     * A 'consumer' type link indicates a link that is to be consumed by the platform's end users,
     * i.e: people wanting to checkout.
     */
    case Consumer = 'consumer';
}
