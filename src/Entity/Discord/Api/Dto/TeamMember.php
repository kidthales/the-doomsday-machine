<?php

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\MembershipState;
use App\Entity\Discord\Api\Enumeration\TeamMemberRole;

/**
 * @see https://discord.com/developers/docs/topics/teams#data-models-team-member-object
 */
class TeamMember
{
    /**
     * @param MembershipState $membership_state User's membership state on the team.
     * @param string $team_id ID of the parent team of which they are a member.
     * @param User $user Avatar, discriminator, ID, and username of the user.
     * @param TeamMemberRole $role Role of the team member.
     */
    public function __construct(
        public MembershipState $membership_state,
        public string          $team_id,
        public User            $user,
        public TeamMemberRole  $role
    )
    {
    }
}
