<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/topics/teams#data-models-membership-state-enum
 */
enum MembershipState: int
{
    case INVITED = 1;
    case ACCEPTED = 2;
}
