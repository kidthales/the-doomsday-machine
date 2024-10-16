<?php

declare(strict_types=1);

namespace App\Discord;

use App\Entity\Discord\Api\Dto\Application;
use App\Entity\Discord\Api\Dto\ApplicationCommand;
use App\Entity\Discord\Api\Dto\CreateGlobalApplicationCommandParams;
use App\Entity\Discord\Api\Dto\CreateGuildApplicationCommandParams;
use App\Entity\Discord\Api\Endpoint\CreateGlobalApplicationCommandEndpoint;
use App\Entity\Discord\Api\Endpoint\CreateGuildApplicationCommandEndpoint;
use App\Entity\Discord\Api\Endpoint\DeleteGlobalApplicationCommandEndpoint;
use App\Entity\Discord\Api\Endpoint\DeleteGuildApplicationCommandEndpoint;
use App\Entity\Discord\Api\Endpoint\GetCurrentApplicationEndpoint;
use App\HttpClient\ApiClient as BaseApiClient;
use App\HttpClient\ApiEndpointInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ValueError;

final class ApiClient extends BaseApiClient
{
    public const string V10 = 'v10';
    public const string V9 = 'v9';

    /**
     * @deprecated
     */
    public const string V8 = 'v8';

    /**
     * @deprecated
     */
    public const string V7 = 'v7';

    /**
     * @deprecated
     */
    public const string V6 = 'v6';

    public const array ALLOWED_VERSIONS = [self::V10, self::V9, self::V8, self::V7, self::V6];

    /**
     * @var string
     */
    private string $apiVersion = self::V10;

    /**
     * @param HttpClientInterface $discordApiClient Autowired to scoped http client 'discord_api.client'.
     * @param SerializerInterface $serializer
     */
    public function __construct(HttpClientInterface $discordApiClient, SerializerInterface $serializer)
    {
        parent::__construct($discordApiClient, $serializer);
    }

    /**
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * @param string $apiVersion
     * @return void
     */
    public function setApiVersion(string $apiVersion): void
    {
        if (!in_array($apiVersion, self::ALLOWED_VERSIONS, true)) {
            throw new ValueError(
                sprintf(
                    'Invalid api version: %s; allowed versions: %s',
                    $apiVersion,
                    implode(', ', self::ALLOWED_VERSIONS)
                )
            );
        }

        $this->apiVersion = $apiVersion;
    }

    /**
     * @param string $applicationId
     * @param CreateGlobalApplicationCommandParams $params
     * @param bool|null $isOverwrite
     * @return ApplicationCommand
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function createGlobalApplicationCommand(
        string                               $applicationId,
        CreateGlobalApplicationCommandParams $params,
        ?bool                                &$isOverwrite = null
    ): ApplicationCommand
    {
        $statusCode = null;

        $body = $this->request(
            new CreateGlobalApplicationCommandEndpoint(applicationId: $applicationId, params: $params),
            $statusCode
        );

        $isOverwrite = $statusCode === 200;

        return $body;
    }

    /**
     * @param string $applicationId
     * @param string $commandId
     * @return null
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function deleteGlobalApplicationCommand(string $applicationId, string $commandId): null
    {
        return $this->request(
            new DeleteGlobalApplicationCommandEndpoint(applicationId: $applicationId, commandId: $commandId)
        );
    }

    /**
     * @param string $applicationId
     * @param string $guildId
     * @param CreateGuildApplicationCommandParams $params
     * @param bool|null $isOverwrite
     * @return ApplicationCommand
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function createGuildApplicationCommand(
        string                              $applicationId,
        string                              $guildId,
        CreateGuildApplicationCommandParams $params,
        ?bool                               &$isOverwrite = null
    ): ApplicationCommand
    {
        $statusCode = null;

        $body = $this->request(
            new CreateGuildApplicationCommandEndpoint(
                applicationId: $applicationId,
                guildId: $guildId,
                params: $params
            ),
            $statusCode
        );

        $isOverwrite = $statusCode === 200;

        return $body;
    }

    /**
     * @param string $applicationId
     * @param string $guildId
     * @param string $commandId
     * @return null
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function deleteGuildApplicationCommand(string $applicationId, string $guildId, string $commandId): null
    {
        return $this->request(
            new DeleteGuildApplicationCommandEndpoint(
                applicationId: $applicationId,
                guildId: $guildId,
                commandId: $commandId
            )
        );
    }

    /**
     * @return Application
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCurrentApplication(): Application
    {
        return $this->request(new GetCurrentApplicationEndpoint());
    }

    /**
     * @param ApiEndpointInterface $endpoint
     * @return string
     */
    protected function getEndpointRequestPath(ApiEndpointInterface $endpoint): string
    {
        return $this->apiVersion . '/' . $endpoint->getRequestPath();
    }
}
