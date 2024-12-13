<?php

namespace App\ApiResource\User;

use ApiPlatform\Metadata as API;
use App\Dto\UserTokenLoginDto;
use App\State\ApiResourceStateProvider;
use App\State\User\UserTokenLoginProcessor;

/**
 * UserTokens authenticate requests on behalf of the User who owns them.\
 * \
 * When a UserToken is created v4 generates a SHA-256 hash that is unique for each UserToken.
 * The value of a UserToken comes preceded by a 4-digit-length prefix based on the type of token it is.
 */
#[API\ApiResource(shortName: 'UserToken', provider: ApiResourceStateProvider::class)]
#[API\Post(input: UserTokenLoginDto::class, processor: UserTokenLoginProcessor::class)]
#[API\Get(security: 'is_granted("USER_OWNED", object)')]
#[API\Delete(security: 'is_granted("USER_OWNED", object)')]
class UserTokenApiResource
{
    #[API\ApiProperty(writable: false, identifier: true)]
    public int $id;

    /**
     * The User token itself.
     */
    #[API\ApiProperty(writable: false)]
    public string $token;

    /**
     * The User who this token grants access as.
     */
    #[API\ApiProperty(writable: false)]
    public UserApiResource $owner;
}
