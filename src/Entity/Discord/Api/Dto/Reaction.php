<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/message#reaction-object-reaction-structure
 */
class Reaction
{
    /**
     * @param int $count Total number of times this emoji has been used to react (including super reacts).
     * @param ReactionCountDetails $count_details Reaction count details object.
     * @param bool $me Whether the current user reacted using this emoji.
     * @param bool $me_burst Whether the current user super-reacted using this emoji.
     * @param Emoji $emoji Emoji information.
     * @param string[] $burst_colors HEX colors used for super reaction.
     */
    public function __construct(
        public int                  $count,
        public ReactionCountDetails $count_details,
        public bool                 $me,
        public bool                 $me_burst,
        public Emoji                $emoji,
        public array                $burst_colors
    )
    {
    }
}
