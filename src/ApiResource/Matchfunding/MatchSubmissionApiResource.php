<?php

namespace App\ApiResource\Matchfunding;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Project\ProjectApiResource;
use App\Entity\Matchfunding\MatchSubmission;
use App\Entity\Matchfunding\MatchSubmissionStatus;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;

/**
 * MatchSubmissions represent the will of a Project to be held under a MatchCall and receive matchfunding financement.
 */
#[API\ApiResource(
    shortName: 'MatchSubmission',
    stateOptions: new Options(entityClass: MatchSubmission::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class
)]
class MatchSubmissionApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The MatchCall to which this MatchSubmission belongs to.
     */
    public MatchCallApiResource $matchCall;

    /**
     * The Project that applied for the MatchCall.
     */
    public ProjectApiResource $project;

    /**
     * The status of the Project's application for the MatchCall.\
     * Only MatchSubmissions with an status `accepted` will receive matchfunding.
     */
    #[API\ApiProperty(securityPostDenormalize: 'is_granted("MATCHSUBMISSION_EDIT", object)')]
    public MatchSubmissionStatus $status;
}
