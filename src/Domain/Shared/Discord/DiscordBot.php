<?php
/*
 * The Doomsday Machine
 * Copyright (C) 2026  Tristan Bonsor
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Domain\Shared\Discord;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
abstract class DiscordBot
{
    protected const string DISCORD_API_VERSION = 'v10';

    protected const int DISCORD_PERMISSIONS = 0;
    protected const int DISCORD_INTEGRATION_TYPE = 0;

    /**
     * @var DiscordApi
     */
    protected readonly DiscordApi $discordApi;

    /**
     * @var array|null
     */
    private ?array $currentApplication = null;

    /**
     * @var array|null
     */
    private ?array $currentUserGuilds = null;

    /**
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->discordApi = new DiscordApi(
            HttpClient::createForBaseUri(
                baseUri: sprintf('https://discord.com/api/%s/', static::DISCORD_API_VERSION),
                defaultOptions: [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => sprintf('Bot %s', $token),
                    ]
                ]
            )
        );
    }

    /**
     * @return string
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getInstallLink(): string
    {
        return sprintf(
            'https://discord.com/oauth2/authorize?client_id=%s&permissions=%d&integration_type=%d&scope=bot',
            $this->getCurrentApplication()['id'],
            static::DISCORD_PERMISSIONS,
            static::DISCORD_INTEGRATION_TYPE
        );
    }

    /**
     * @param bool $refresh
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCurrentApplication(bool $refresh = false): array
    {
        if ($refresh || $this->currentApplication === null) {
            $this->currentApplication = $this->discordApi->getCurrentApplication()->toArray();
        }
        return $this->currentApplication;
    }

    /**
     * @param bool $refresh
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCurrentUserGuilds(bool $refresh = false): array
    {
        if ($refresh || $this->currentUserGuilds === null) {
            $limit = 200;
            $guilds = [];
            $page = $this->discordApi->getCurrentUserGuilds(limit: $limit)->toArray();
            while (true) {
                $guilds = [...$guilds, ...$page];
                if (count($page) !== $limit) {
                    break;
                }
                $page = $this->discordApi->getCurrentUserGuilds(after: $page[$limit - 1]['id'], limit: $limit)->toArray();
            }
            $this->currentUserGuilds = $guilds;
        }
        return $this->currentUserGuilds;
    }
}
