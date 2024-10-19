<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Enumeration;

/**
 * @see https://discord.com/developers/docs/resources/poll#layout-type
 */
enum LayoutType: int
{
    /**
     * The, uhm, default layout type.
     */
    case DEFAULT = 1;
}
