<?php

namespace App\Entity\Matchfunding;

enum MatchSubmissionStatus: string
{
    /**
     * The MatchSubmission is under review for the MatchCall.
     */
    case InReview = 'in_review';

    /**
     * The MatchSubmission was accepted into the MatchCall.
     */
    case Accepted = 'accepted';

    /**
     * The MatchSubmission was rejected out of the MatchCall.
     */
    case Rejected = 'rejected';
}
