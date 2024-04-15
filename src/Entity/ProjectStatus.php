<?php

namespace App\Entity;

enum ProjectStatus: int
{
    case REJECTED = 0;
    case EDITING = 1;
    case REVIEWING = 2;
    case IN_CAMPAIGN = 3;
    case FUNDED = 4;
    case FULFILLED = 5;
    case UNFUNDED = 6;
}
