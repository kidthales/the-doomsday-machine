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

use App\Domain\Shared\Console\Question\ChoicesResolver;
use Godruoyi\Snowflake\Snowflake;
use Godruoyi\Snowflake\SnowflakeException;
use InvalidArgumentException;
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
     * @var array<string, array>|null
     */
    private ?array $currentUserGuildByGuildId = null;

    /**
     * @var array<string, array>
     */
    private array $guildChannels = [];

    /**
     * @var array<string, array>
     */
    private ?array $guildChannelByChannelId = [];

    /**
     * @var array<string, array>
     */
    private array $activeGuildThreads = [];

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

    /**
     * @param bool $refresh
     * @return ChoicesResolver
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCurrentUserGuildsChoicesResolver(bool $refresh = false): ChoicesResolver
    {
        $guildIdByName = array_reduce(
            $this->getCurrentUserGuilds($refresh),
            function (array $carry, array $guild) {
                $carry[$guild['name']] = $guild['id'];
                return $carry;
            },
            []
        );
        ksort($guildIdByName);
        return new ChoicesResolver($guildIdByName);
    }

    /**
     * @param string $guildId
     * @param bool $refresh
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCurrentUserGuild(string $guildId, bool $refresh = false): array
    {
        if ($refresh || $this->currentUserGuildByGuildId === null) {
            $this->currentUserGuildByGuildId = array_reduce(
                $this->getCurrentUserGuilds($refresh),
                function (array $carry, array $guild): array {
                    $carry[$guild['id']] = $guild;
                    return $carry;
                },
                []
            );
        }
        return $this->currentUserGuildByGuildId[$guildId] ?? throw new InvalidArgumentException('Guild id not found in current user guilds');
    }

    /**
     * @param string $guildId
     * @param bool $refresh
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getGuildChannels(string $guildId, bool $refresh = false): array
    {
        if (!in_array($guildId, array_map(fn(array $guild) => $guild['id'], $this->getCurrentUserGuilds($refresh)))) {
            throw new InvalidArgumentException('Guild id not found in current user guilds');
        }
        if ($refresh || !isset($this->guildChannels[$guildId])) {
            $this->guildChannels[$guildId] = $this->discordApi->getGuildChannels($guildId)->toArray();
        }
        return $this->guildChannels[$guildId];
    }

    /**
     * @param string $guildId
     * @param bool $refresh
     * @return ChoicesResolver
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getGuildChannelsChoicesResolver(string $guildId, bool $refresh = false): ChoicesResolver
    {
        $channels = $this->getGuildChannels($guildId, $refresh);
        $channelCategoryNameById = [];
        foreach ($channels as $channel) {
            if ($channel['type'] === DiscordApi::CHANNEL_TYPE_GUILD_CATEGORY) {
                $channelCategoryNameById[$channel['id']] = $channel['name'];
            }
        }
        $channelIdByName = [];
        foreach ($channels as $channel) {
            if ($channel['type'] !== DiscordApi::CHANNEL_TYPE_GUILD_CATEGORY) {
                $channelNamePrefix = isset($channel['parent_id']) && isset($channelCategoryNameById[$channel['parent_id']])
                    ? sprintf('%s  >  ', $channelCategoryNameById[$channel['parent_id']])
                    : '';
                $channelIdByName[$channelNamePrefix . $channel['name']] = $channel['id'];
            }
        }
        ksort($channelIdByName);
        return new ChoicesResolver($channelIdByName);
    }

    /**
     * @param string $channelId
     * @param bool $refresh
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getGuildChannel(string $channelId, bool $refresh = false): array
    {
        if ($refresh || !isset($this->guildChannelByChannelId[$channelId])) {
            $channels = $this->getGuildChannels($this->discordApi->getChannel($channelId)->toArray()['guild_id'], $refresh);
            foreach ($channels as $channel) {
                $this->guildChannelByChannelId[$channel['id']] = $channel;
            }
        }
        return $this->guildChannelByChannelId[$channelId] ?? throw new InvalidArgumentException('Channel id not found in current user guilds');
    }

    /**
     * @param string $guildId
     * @param bool $refresh
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function listActiveGuildThreads(string $guildId, bool $refresh = false): array
    {
        if (!in_array($guildId, array_map(fn(array $guild) => $guild['id'], $this->getCurrentUserGuilds($refresh)))) {
            throw new InvalidArgumentException('Guild id not found in current user guilds');
        }
        if ($refresh || !isset($this->activeGuildThreads[$guildId])) {
            $this->activeGuildThreads[$guildId] = $this->discordApi->listActiveGuildThreads($guildId)->toArray();
        }
        return $this->activeGuildThreads[$guildId];
    }

    /**
     * @return Snowflake
     * @throws SnowflakeException
     */
    protected function createSnowflake(): Snowflake
    {
        // Discord Epoch: Jan 1, 2015 in milliseconds
        return (new Snowflake())->setStartTimeStamp(1420070400000);
    }
}
