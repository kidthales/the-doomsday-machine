<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

/**
 * @see https://discord.com/developers/docs/resources/application#application-object-application-integration-type-configuration-object
 */
class ApplicationIntegrationTypeConfiguration
{
    /**
     * @param InstallParams|null $oauth2_install_params Install params for each installation context's default in-app
     * authorization link.
     */
    public function __construct(public ?InstallParams $oauth2_install_params = null)
    {
    }
}
