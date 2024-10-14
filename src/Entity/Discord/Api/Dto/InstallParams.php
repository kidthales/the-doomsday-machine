<?php

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/application#install-params-object-install-params-structure
 */
class InstallParams
{
    /**
     * @param string[] $scopes Scopes to add the application to the server with.
     * @param string $permissions Permissions to request for the bot role.
     */
    public function __construct(public array $scopes, public string $permissions)
    {
    }
}
