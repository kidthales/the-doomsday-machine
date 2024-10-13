<?php

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/application#application-object-application-structure
 */
class Application
{
    /**
     * @param string $id ID of the app.
     * @param string $name Name of the app.
     * @param string|null $icon Icon hash of the app.
     * @param string $description Description of the app.
     * @param bool $bot_public When false, only the app owner can add the app to guilds.
     * @param bool $bot_require_code_grant When true, the app's bot will only join upon completion of the full OAuth2
     * code grant flow.
     * @param string $verify_key Hex encoded key for verification in interactions and the GameSDK's GetTicket.
     * @param Team|null $team If the app belongs to a team, this will be a list of the members of that team.
     * @param string[]|null $rpc_origins List of RPC origin URLs, if RPC is enabled.
     * @param User|null $bot Partial user object for the bot user associated with the app.
     * @param string|null $terms_of_service_url URL of the app's Terms of Service.
     * @param string|null $privacy_policy_url URL of the app's Privacy Policy.
     * @param User|null $owner Partial user object for the owner of the app.
     * @param string|null $guild_id Guild associated with the app. For example, a developer support server.
     * @param Guild|null $guild Partial object of the associated guild.
     * @param string|null $primary_sku_id If this app is a game sold on Discord, this field will be the id of the
     * "Game SKU" that is created, if exists.
     * @param string|null $slug If this app is a game sold on Discord, this field will be the URL slug that links to the
     * store page.
     * @param string|null $cover_image App's default rich presence invite cover image hash.
     * @param int|null $flags App's public flags.
     * @param int|null $approximate_guild_count Approximate count of guilds the app has been added to.
     * @param int|null $approximate_user_install_count Approximate count of users that have installed the app.
     * @param string[]|null $redirect_uris Array of redirect URIs for the app.
     * @param string|null $interactions_endpoint_url Interactions endpoint URL for the app.
     * @param string|null $role_connections_verification_url Role connection verification URL for the app.
     * @param string[]|null $tags List of tags describing the content and functionality of the app. Max of 5 tags.
     * @param InstallParams|null $install_params Settings for the app's default in-app authorization link,
     * if enabled.
     * @param ApplicationIntegrationTypeConfiguration[]|null $integration_types_config Default scopes
     * and permissions for each supported installation context. Value for each key is an integration type configuration
     * object.
     * @param string|null $custom_install_url Default custom authorization URL for the app, if enabled.
     */
    public function __construct(
        public string         $id,
        public string         $name,
        public ?string        $icon,
        public string         $description,
        public bool           $bot_public,
        public bool           $bot_require_code_grant,
        public string         $verify_key,
        public ?Team          $team,
        public ?array         $rpc_origins = null,
        public ?User          $bot = null,
        public ?string        $terms_of_service_url = null,
        public ?string        $privacy_policy_url = null,
        public ?User          $owner = null,
        public ?string        $guild_id = null,
        public ?Guild         $guild = null,
        public ?string        $primary_sku_id = null,
        public ?string        $slug = null,
        public ?string        $cover_image = null,
        public ?int           $flags = null,
        public ?int           $approximate_guild_count = null,
        public ?int           $approximate_user_install_count = null,
        public ?array         $redirect_uris = null,
        public ?string        $interactions_endpoint_url = null,
        public ?string        $role_connections_verification_url = null,
        public ?array         $tags = null,
        public ?InstallParams $install_params = null,
        public ?array         $integration_types_config = null,
        public ?string        $custom_install_url = null,
    )
    {
    }
}
