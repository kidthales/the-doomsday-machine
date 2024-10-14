<?php

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/topics/teams#data-models-team-object
 */
class Team
{
    /**
     * @param string|null $icon Hash of the image of the team's icon.
     * @param string $id Unique ID of the team
     * @param TeamMember[] $members Members of the team.
     * @param string $name Name of the team.
     * @param string $owner_user_id User ID of the current team owner.
     */
    public function __construct(
        public ?string $icon,
        public string  $id,
        public array   $members,
        public string  $name,
        public string  $owner_user_id
    )
    {
    }
}
