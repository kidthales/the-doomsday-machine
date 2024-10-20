<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/message#reaction-count-details-object-reaction-count-details-structure
 */
class ReactionCountDetails
{
    /**
     * @param int $burst Count of super reactions.
     * @param int $normal Count of normal reactions.
     */
    public function __construct(public int $burst, public int $normal)
    {
    }
}
