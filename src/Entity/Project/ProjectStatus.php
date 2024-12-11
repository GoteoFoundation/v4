<?php

namespace App\Entity\Project;

/**
 * Projects have a start and an end, and in the meantime they go through different phases represented under this status.
 */
enum ProjectStatus: string
{
    /**
     * Project is under edition by it's owner.
     */
    case InEditing = 'in_editing';

    /**
     * Owner finished editing and an admin is reviewing it.
     */
    case InReview = 'in_review';

    /**
     * An admin reviewed it and rejected it. Final.
     */
    case Rejected = 'rejected';

    /**
     * Project was reviewed and is in campaign for funding.
     */
    case InCampaign = 'in_campaign';

    /**
     * Project finished campaigning but didn't meet funding goals. Final.
     */
    case Unfunded = 'unfunded';

    /**
     * Project successfully finished campaigning and owner can receive funds.
     */
    case InFunding = 'in_funding';

    /**
     * Project owner received funds and fulfilled their goals. Final.
     */
    case Fulfilled = 'fulfilled';
}
