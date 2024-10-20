<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/message#embed-object-embed-field-structure
 */
class EmbedField
{
    /**
     * @param string $name Name of the field.
     * @param string $value Value of the field.
     * @param bool|null $inline Whether or not this field should display inline.
     */
    public function __construct(public string $name, public string $value, public ?bool $inline = null)
    {
    }
}
