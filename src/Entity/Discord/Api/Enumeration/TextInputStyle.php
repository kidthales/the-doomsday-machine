<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#text-input-object-text-input-styles
 */
enum TextInputStyle: int
{
    /**
     * Single-line input.
     */
    case Short = 1;

    /**
     * Multi-line input.
     */
    case Paragraph = 2;
}
