<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * https://discord.com/developers/docs/resources/message#embed-object-embed-provider-structure
 */
class EmbedProvider
{
    /**
     * @param string|null $name Name of provider.
     * @param string|null $url URL of provider.
     */
    public function __construct(public ?string $name = null, public ?string $url = null)
    {
    }
}
