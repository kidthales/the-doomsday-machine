<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Endpoint;

use App\Entity\Discord\Api\Dto\ApplicationCommand;
use App\Entity\Discord\Api\Dto\CreateGuildApplicationCommandParams;
use App\HttpClient\AbstractJsonApiEndpoint;

/**
 * @see https://discord.com/developers/docs/interactions/application-commands#create-guild-application-command
 */
class CreateGuildApplicationCommandEndpoint extends AbstractJsonApiEndpoint
{
    /**
     * @return string
     */
    public static function getRequestMethod(): string
    {
        return 'POST';
    }

    /**
     * @param string $applicationId
     * @param string $guildId
     * @param CreateGuildApplicationCommandParams $params
     */
    public function __construct(
        public string                              $applicationId,
        public string                              $guildId,
        public CreateGuildApplicationCommandParams $params
    )
    {
    }

    /**
     * @return CreateGuildApplicationCommandParams
     */
    public function getRequestBody(): CreateGuildApplicationCommandParams
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getRequestPath(): string
    {
        return sprintf('applications/%s/guilds/%s/commands', $this->applicationId, $this->guildId);
    }

    /**
     * @return string
     */
    public function getResponseBodyType(): string
    {
        return ApplicationCommand::class;
    }

    /**
     * @return bool
     */
    public function hasRequestBody(): bool
    {
        return true;
    }
}
