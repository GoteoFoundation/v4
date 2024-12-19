<?php

namespace App\ApiResource\Matchfunding;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata as API;
use App\ApiResource\Project\ProjectApiResource;
use App\Entity\Matchfunding\MatchCallSubmission;
use App\Entity\Matchfunding\MatchCallSubmissionStatus;
use App\State\ApiResourceStateProcessor;
use App\State\ApiResourceStateProvider;

/**
 * MatchCallSubmissions represent the will of a Project to be held under a MatchCall and receive matchfunding financement.
 */
#[API\ApiResource(
    shortName: 'MatchCallSubmission',
    stateOptions: new Options(entityClass: MatchCallSubmission::class),
    provider: ApiResourceStateProvider::class,
    processor: ApiResourceStateProcessor::class
)]
class MatchCallSubmissionApiResource
{
    #[API\ApiProperty(identifier: true, writable: false)]
    public int $id;

    /**
     * The MatchCall to which this MatchCallSubmission belongs to.
     */
    public MatchCallApiResource $matchCall;

    /**
     * The Project that applied for the MatchCall.
     */
    public ProjectApiResource $project;

    /**
     * The status of the Project's application for the MatchCall.\
     * Only MatchCallSubmissions with an status `accepted` will receive matchfunding.
     */
    #[API\ApiProperty(securityPostDenormalize: 'is_granted("MATCHCALLSUBMISSION_EDIT", object)')]
    public MatchCallSubmissionStatus $status;
}
