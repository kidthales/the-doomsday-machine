<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/poll#poll-results-object-poll-results-object-structure
 */
class PollResults
{
    /**
     * @param bool $is_finalized Whether the votes have been precisely counted.
     * @param PollAnswerCount[] $answer_counts The counts for each answer.
     */
    public function __construct(public bool $is_finalized, public array $answer_counts)
    {
    }
}
