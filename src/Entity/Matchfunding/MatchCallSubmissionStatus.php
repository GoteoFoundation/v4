<?php

namespace App\Entity\Matchfunding;

enum MatchCallSubmissionStatus: string
{
    /**
     * The MatchCallSubmission is under review for the MatchCall.
     */
    case InReview = 'in_review';

    /**
     * The MatchCallSubmission was accepted into the MatchCall.
     */
    case Accepted = 'accepted';

    /**
     * The MatchCallSubmission was rejected out of the MatchCall.
     */
    case Rejected = 'rejected';
}
