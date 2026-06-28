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
    public const string DISCORD_API_VERSION = 'v10';

    /**
     * @var DiscordApiClient
     */
    protected readonly DiscordApiClient $discordApi;

    /**
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->discordApi = new DiscordApiClient(
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
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getInfo(): array
    {
        return $this->discordApi->getCurrentApplication()->toArray();
    }
}
