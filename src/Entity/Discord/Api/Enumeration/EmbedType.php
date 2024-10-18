<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/message#embed-object-embed-types
 */
enum EmbedType: string
{
    /**
     * Generic embed rendered from embed attributes.
     */
    case rich = 'rich';

    /**
     * Image embed.
     */
    case image = 'image';

    /**
     * Video embed.
     */
    case video = 'video';

    /**
     * Animated gif image embed rendered as a video embed.
     */
    case glfv = 'glfv';

    /**
     * Article embed.
     */
    case article = 'article';

    /**
     * Link embed.
     */
    case link = 'link';

    /**
     * Poll result embed.
     */
    case poll_result = 'poll_result';
}
