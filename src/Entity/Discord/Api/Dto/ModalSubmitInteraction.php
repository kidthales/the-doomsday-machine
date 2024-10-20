<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\InteractionContextType;
use App\Entity\Discord\Api\Enumeration\InteractionType;

/**
 * MODAL_SUBMIT interaction.
 * @see https://discord.com/developers/docs/interactions/receiving-and-responding#interaction-object-interaction-data
 */
class ModalSubmitInteraction extends AbstractInteraction
{
    /**
     * @param string $id ID of the interaction.
     * @param string $application_id ID of the application this interaction is for.
     * @param string $token Continuation token for responding to the interaction.
     * @param string $app_permissions Bitwise set of permissions the app has in the source location of the interaction.
     * @param Entitlement[] $entitlements For monetized apps, any entitlements for the invoking user, representing
     * access to premium SKUs.
     * @param string[] $authorizing_integration_owners Mapping of installation contexts that the interaction was
     * authorized for to related user or guild IDs. See Authorizing Integration Owners Object for details.
     * @param ModalSubmitData $data Interaction data payload.
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
     */
    public function __construct(
        string                  $id,
        string                  $application_id,
        string                  $token,
        string                  $app_permissions,
        array                   $entitlements,
        array                   $authorizing_integration_owners,
        public ModalSubmitData  $data,
        ?Guild                  $guild = null,
        ?string                 $guild_id = null,
        ?Channel                $channel = null,
        ?string                 $channel_id = null,
        ?GuildMember            $member = null,
        ?User                   $user = null,
        ?Message                $message = null,
        ?string                 $locale = null,
        ?string                 $guild_locale = null,
        ?InteractionContextType $context = null
    )
    {
        parent::__construct(
            id: $id,
            application_id: $application_id,
            type: InteractionType::MODAL_SUBMIT,
            token: $token,
            app_permissions: $app_permissions,
            entitlements: $entitlements,
            authorizing_integration_owners: $authorizing_integration_owners,
            guild: $guild,
            guild_id: $guild_id,
            channel: $channel,
            channel_id: $channel_id,
            member: $member,
            user: $user,
            message: $message,
            locale: $locale,
            guild_locale: $guild_locale,
            context: $context
        );
    }
}
