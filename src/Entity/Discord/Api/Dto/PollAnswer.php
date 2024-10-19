<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/poll#poll-answer-object-poll-answer-object-structure
 */
class PollAnswer
{
    /**
     * @param int $answer_id The ID of the answer.
     * @param PollMedia $poll_media The data of the answer.
     */
    public function __construct(public int $answer_id, public PollMedia $poll_media)
    {
    }
}
