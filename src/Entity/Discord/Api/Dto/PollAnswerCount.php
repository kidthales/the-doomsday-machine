<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/poll#poll-results-object-poll-answer-count-object-structure
 */
class PollAnswerCount
{
    /**
     * @param int $id The answer_id.
     * @param int $count The number of votes for this answer.
     * @param bool $me_voted Whether the current user voted for this answer.
     */
    public function __construct(public int $id, public int $count, public bool $me_voted)
    {
    }
}
