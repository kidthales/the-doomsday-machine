<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\OverwriteType;

/**
 * @see https://discord.com/developers/docs/resources/channel#overwrite-object-overwrite-structure
 */
class Overwrite
{
    /**
     * @param string $id Role or user id.
     * @param OverwriteType $type Either 0 (role) or 1 (member).
     * @param string $allow Permission bit set.
     * @param string $deny Permission bit set.
     */
    public function __construct(
        public string        $id,
        public OverwriteType $type,
        public string        $allow,
        public string        $deny
    )
    {
    }
}
