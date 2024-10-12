<?php

namespace App\Discord;

use App\HttpClient\ApiClient as BaseApiClient;
use App\HttpClient\ApiEndpointInterface;
use Symfony\Component\Serializer\SerializerInterface;
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
     * @param ApiEndpointInterface $endpoint
     * @return string
     */
    protected function getEndpointRequestPath(ApiEndpointInterface $endpoint): string
    {
        return $this->apiVersion . '/' . $endpoint->getRequestPath();
    }
}
