<?php

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/sticker#sticker-object-sticker-format-types
 */
enum StickerFormatType: int
{
    case PNG = 1;
    case APNG = 2;
    case LOTTIE = 3;
    case GIF = 4;
}
