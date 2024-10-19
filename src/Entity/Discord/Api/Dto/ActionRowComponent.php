<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ComponentType;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#action-rows
 */
class ActionRowComponent extends AbstractComponent
{
    /**
     * @param AbstractComponent[] $components Message components (button: 2, text select: 3,
     * text input: 4, user select: 5, role select: 6, mentionable select: 7, channels select: 8).
     */
    public function __construct(public array $components)
    {
        parent::__construct(type: ComponentType::ActionRow);
    }
}
