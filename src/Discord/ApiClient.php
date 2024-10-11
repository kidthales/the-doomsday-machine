<?php

namespace App\Discord;

use App\HttpClient\ApiClient as BaseApiClient;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

    /**
     * @param HttpClientInterface $discordApiClient Autowired to scoped http client 'discord_api.client'.
     * @param SerializerInterface $serializer
     */
    public function __construct(HttpClientInterface $discordApiClient, SerializerInterface $serializer)
    {
        parent::__construct($discordApiClient, $serializer);
    }
}
