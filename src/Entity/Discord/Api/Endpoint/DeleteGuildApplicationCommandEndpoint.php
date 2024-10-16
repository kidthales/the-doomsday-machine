<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Endpoint;

use App\HttpClient\AbstractJsonApiEndpoint;

/**
 * @see https://discord.com/developers/docs/interactions/application-commands#delete-guild-application-command
 */
class DeleteGuildApplicationCommandEndpoint extends AbstractJsonApiEndpoint
{
    /**
     * @return string
     */
    public static function getRequestMethod(): string
    {
        return 'DELETE';
    }

    /**
     * @param string $applicationId
     * @param string $guildId
     * @param string $commandId
     */
    public function __construct(public string $applicationId, public string $guildId, public string $commandId)
    {
    }

    /**
     * @return null
     */
    public function getRequestBody(): null
    {
        return null;
    }

    /**
     * @return string
     */
    public function getRequestPath(): string
    {
        return sprintf('applications/%s/guilds/%s/commands/%s', $this->applicationId, $this->guildId, $this->commandId);
    }

    /**
     * @return null
     */
    public function getResponseBodyType(): null
    {
        return null;
    }

    /**
     * @return bool
     */
    public function hasRequestBody(): bool
    {
        return false;
    }
}
