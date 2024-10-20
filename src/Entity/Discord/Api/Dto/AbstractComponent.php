<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\ComponentType;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

/**
 * @see https://discord.com/developers/docs/interactions/message-components#message-components
 */
#[DiscriminatorMap(typeProperty: 'type', mapping: [
    ComponentType::ActionRow->value => ActionRowComponent::class,
    ComponentType::Button->value => ButtonComponent::class,
    ComponentType::TextInput->value => TextInputComponent::class,
    ComponentType::StringSelect->value => SelectMenuComponent::class,
    ComponentType::UserSelect->value => SelectMenuComponent::class,
    ComponentType::RoleSelect->value => SelectMenuComponent::class,
    ComponentType::MentionableSelect->value => SelectMenuComponent::class,
    ComponentType::ChannelSelect->value => SelectMenuComponent::class
])]
abstract class AbstractComponent
{
    /**
     * @param ComponentType $type The message component type.
     */
    public function __construct(public ComponentType $type)
    {
    }
}
