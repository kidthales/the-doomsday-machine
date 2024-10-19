<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\InteractionType;

/**
 * @see https://discord.com/developers/docs/resources/message#message-interaction-metadata-object-message-interaction-metadata-structure
 */
class MessageInteractionMetadata
{
    /**
     * @param string $id ID of the interaction.
     * @param InteractionType $type Type of interaction.
     * @param User $user User who triggered the interaction.
     * @param string[] $authorizing_integration_owners IDs for installation context(s) related to an interaction.
     * Details in Authorizing Integration Owners Object.
     * @param string|null $original_response_message_id ID of the original response message, present only on follow-up
     * messages.
     * @param string|null $interacted_message_id ID of the message that contained interactive component, present only on
     * messages created from component interactions.
     * @param MessageInteractionMetadata|null $triggering_interaction_metadata Metadata for the interaction that was
     * used to open the modal, present only on modal submit interactions.
     */
    public function __construct(
        public string                      $id,
        public InteractionType             $type,
        public User                        $user,
        public array                       $authorizing_integration_owners,
        public ?string                     $original_response_message_id = null,
        public ?string                     $interacted_message_id = null,
        public ?MessageInteractionMetadata $triggering_interaction_metadata = null
    )
    {
    }
}
