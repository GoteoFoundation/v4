<?php

namespace App\Entity;

enum ProjectStatus: string
{
    case Rejected = 'rejected';
    case Editing = 'editing';
    case Reviewing = 'reviewing';
    case InCampaign = 'in_campaign';
    case Funded = 'funded';
    case Fulfilled = 'fulfilled';
    case Unfunded = 'unfunded';
}
