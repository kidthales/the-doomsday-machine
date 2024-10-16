<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Endpoint;

use App\Entity\Discord\Api\Dto\ApplicationCommand;
use App\Entity\Discord\Api\Dto\CreateGlobalApplicationCommandParams;
use App\HttpClient\AbstractJsonApiEndpoint;

/**
 * @see https://discord.com/developers/docs/interactions/application-commands#create-global-application-command
 */
class CreateGlobalApplicationCommandEndpoint extends AbstractJsonApiEndpoint
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
     * @param CreateGlobalApplicationCommandParams $params
     */
    public function __construct(
        public string                               $applicationId,
        public CreateGlobalApplicationCommandParams $params
    )
    {
    }

    /**
     * @return CreateGlobalApplicationCommandParams
     */
    public function getRequestBody(): CreateGlobalApplicationCommandParams
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getRequestPath(): string
    {
        return sprintf('applications/%s/commands', $this->applicationId);
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
