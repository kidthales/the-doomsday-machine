<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\InteractionContextType;
use App\Entity\Discord\Api\Enumeration\InteractionType;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

/**
 * @see https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-interaction-structure
 */
#[DiscriminatorMap(typeProperty: 'type', mapping: [
    InteractionType::PING->value => PingInteraction::class,
    InteractionType::APPLICATION_COMMAND->value => ApplicationCommandInteraction::class,
    InteractionType::APPLICATION_COMMAND_AUTOCOMPLETE->value => ApplicationCommandInteraction::class,
    InteractionType::MESSAGE_COMPONENT->value => MessageComponentInteraction::class,
    InteractionType::MODAL_SUBMIT->value => ModalSubmitInteraction::class
])]
abstract class AbstractInteraction
{
    /**
     * @param string $id ID of the interaction.
     * @param string $application_id ID of the application this interaction is for.
     * @param InteractionType $type Type of interaction.
     * @param string $token Continuation token for responding to the interaction.
     * @param string $app_permissions Bitwise set of permissions the app has in the source location of the interaction.
     * @param Entitlement[] $entitlements For monetized apps, any entitlements for the invoking user, representing
     * access to premium SKUs.
     * @param string[] $authorizing_integration_owners Mapping of installation contexts that the interaction was
     * authorized for to related user or guild IDs. See Authorizing Integration Owners Object for details.
     * @param Guild|null $guild Guild that the interaction was sent from.
     * @param string|null $guild_id Guild that the interaction was sent from.
     * @param Channel|null $channel Channel that the interaction was sent from.
     * @param string|null $channel_id Channel that the interaction was sent from.
     * @param GuildMember|null $member Guild member data for the invoking user, including permissions.
     * @param User|null $user User object for the invoking user, if invoked in a DM.
     * @param Message|null $message For components, the message they were attached to.
     * @param string|null $locale Selected language of the invoking user.
     * @param string|null $guild_locale Guild's preferred locale, if invoked in a guild.
     * @param InteractionContextType|null $context Context where the interaction was triggered from.
     * @param int $version Read-only property, always 1.
     */
    public function __construct(
        public string                  $id,
        public string                  $application_id,
        public InteractionType         $type,
        public string                  $token,
        public string                  $app_permissions,
        public array                   $entitlements,
        public array                   $authorizing_integration_owners,
        public ?Guild                  $guild = null,
        public ?string                 $guild_id = null,
        public ?Channel                $channel = null,
        public ?string                 $channel_id = null,
        public ?GuildMember            $member = null,
        public ?User                   $user = null,
        public ?Message                $message = null,
        public ?string                 $locale = null,
        public ?string                 $guild_locale = null,
        public ?InteractionContextType $context = null,
        public readonly int            $version = 1
    )
    {
    }
}
