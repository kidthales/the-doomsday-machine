<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\LayoutType;

/**
 * @see https://discord.com/developers/docs/resources/poll#poll-object-poll-object-structure
 */
class Poll
{
    /**
     * @param PollMedia $question The question of the poll. Only text is supported.
     * @param PollAnswer[] $answers Each of the answers available in the poll.
     * @param string|null $expiry The time when the poll ends.
     * @param bool $allow_multiselect Whether a user can select multiple answers.
     * @param LayoutType $layout_type The layout type of the poll.
     * @param PollResults|null $results The results of the poll.
     */
    public function __construct(
        public PollMedia    $question,
        public array        $answers,
        public ?string      $expiry,
        public bool         $allow_multiselect,
        public LayoutType   $layout_type,
        public ?PollResults $results = null
    )
    {
    }
}
