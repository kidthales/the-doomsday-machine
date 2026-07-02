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

use DateTimeImmutable;
use Symfony\Component\HttpClient\ThrottlingHttpClient;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class DiscordApi
{
    public const int CHANNEL_TYPE_GUILD_TEXT = 0;
    public const int CHANNEL_TYPE_DM = 1;
    public const int CHANNEL_TYPE_GUILD_VOICE = 2;
    public const int CHANNEL_TYPE_GROUP_DM = 3;
    public const int CHANNEL_TYPE_GUILD_CATEGORY = 4;
    public const int CHANNEL_TYPE_GUILD_ANNOUNCEMENT = 5;
    public const int CHANNEL_TYPE_ANNOUNCEMENT_THREAD = 10;
    public const int CHANNEL_TYPE_PUBLIC_THREAD = 11;
    public const int CHANNEL_TYPE_PRIVATE_THREAD = 12;
    public const int CHANNEL_TYPE_GUILD_STAGE_VOICE = 13;
    public const int CHANNEL_TYPE_GUILD_DIRECTORY = 14;
    public const int CHANNEL_TYPE_GUILD_FORUM = 15;
    public const int CHANNEL_TYPE_GUILD_MEDIA = 16;

    /**
     * @param string $timestamp
     * @return DateTimeImmutable
     */
    public static function parseDiscordTimestamp(string $timestamp): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.uP', $timestamp);
        return $date ?: DateTimeImmutable::createFromFormat('Y-m-d\TH:i:sP', $timestamp);
    }

    /**
     * @var ThrottlingHttpClient
     */
    private ThrottlingHttpClient $httpClient;

    /**
     * @param HttpClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        // TODO: retry_after backoff logic...
        $this->httpClient = new ThrottlingHttpClient(
            $httpClient,
            (new RateLimiterFactory([
                'id' => 'discord_api_limiter',
                'policy' => 'token_bucket',
                'limit' => 1,
                'rate' => ['interval' => '1 second', 'amount' => 1]
            ], new InMemoryStorage()))->create()
        );
    }

    ////////////////////////////////////////////////////////////////////////////
    // Application
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @see https://discord.com/developers/docs/resources/application#get-current-application
     */
    public function getCurrentApplication(): ResponseInterface
    {
        return $this->request('GET', 'applications/@me');
    }

    ////////////////////////////////////////////////////////////////////////////
    // Audit Log
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $guildId
     * @param string|null $userId
     * @param int|null $actionType
     * @param string|null $before
     * @param string|null $after
     * @param int|null $limit
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @see https://docs.discord.com/developers/resources/audit-log#get-guild-audit-log
     */
    public function getGuildAuditLog(
        string  $guildId,
        ?string $userId = null,
        ?int    $actionType = null,
        ?string $before = null,
        ?string $after = null,
        ?int    $limit = null
    ): ResponseInterface
    {
        $query = array_filter(
            ['user_id' => $userId, 'action_type' => $actionType, 'before' => $before, 'after' => $after, 'limit' => $limit],
            fn(mixed $value) => $value !== null
        );
        return $this->request('GET', sprintf('guilds/%s/audit-logs', $guildId), ['query' => $query]);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Channel
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $channelId
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @see https://docs.discord.com/developers/resources/channel#get-channel
     */
    public function getChannel(string $channelId): ResponseInterface
    {
        return $this->request('GET', sprintf('channels/%s', $channelId));
    }

    ////////////////////////////////////////////////////////////////////////////
    // Guild
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $guildId
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @see https://docs.discord.com/developers/resources/guild#get-guild-channels
     */
    public function getGuildChannels(string $guildId): ResponseInterface
    {
        return $this->request('GET', sprintf('guilds/%s/channels', $guildId));
    }

    /**
     * @param string $guildId
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @see https://docs.discord.com/developers/resources/guild#list-active-guild-threads
     */
    public function listActiveGuildThreads(string $guildId): ResponseInterface
    {
        return $this->request('GET', sprintf('guilds/%s/threads/active', $guildId));
    }

    ////////////////////////////////////////////////////////////////////////////
    // Message
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $channelId
     * @param string|null $around
     * @param string|null $before
     * @param string|null $after
     * @param int|null $limit
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @see https://discord.com/developers/docs/resources/message#get-channel-messages
     */
    public function getChannelMessages(
        string  $channelId,
        ?string $around = null,
        ?string $before = null,
        ?string $after = null,
        ?int    $limit = null
    ): ResponseInterface
    {
        $query = array_filter(
            ['around' => $around, 'before' => $before, 'after' => $after, 'limit' => $limit],
            fn(mixed $value) => $value !== null
        );
        return $this->request('GET', sprintf('channels/%s/messages', $channelId), ['query' => $query]);
    }

    /**
     * @param string $channelId
     * @param string $messageId
     * @param string|null $reason
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @see https://discord.com/developers/docs/resources/message#delete-message
     */
    public function deleteMessage(string $channelId, string $messageId, ?string $reason = null): ResponseInterface
    {
        $headers = [];
        if ($reason !== null) {
            $headers['X-Audit-Log-Reason'] = $reason;
        }
        return $this->request(
            'DELETE',
            sprintf('channels/%s/messages/%s', $channelId, $messageId),
            ['headers' => $headers]
        );
    }

    /**
     * @param string $channelId
     * @param string[] $messages
     * @param string|null $reason
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @see https://discord.com/developers/docs/resources/message#bulk-delete-messages
     */
    public function bulkDeleteMessages(string $channelId, array $messages, ?string $reason = null): ResponseInterface
    {
        $headers = [];
        if ($reason !== null) {
            $headers['X-Audit-Log-Reason'] = $reason;
        }
        return $this->request(
            'POST',
            sprintf('channels/%s/messages/bulk-delete', $channelId),
            [
                'headers' => $headers,
                'json' => ['messages' => $messages]
            ]
        );
    }

    ////////////////////////////////////////////////////////////////////////////
    // User
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @param string|null $before
     * @param string|null $after
     * @param int|null $limit
     * @param bool|null $withCounts
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @see https://docs.discord.com/developers/resources/user#get-current-user-guilds
     */
    public function getCurrentUserGuilds(
        ?string $before = null,
        ?string $after = null,
        ?int    $limit = null,
        ?bool   $withCounts = null
    ): ResponseInterface
    {
        $query = array_filter(
            ['before' => $before, 'after' => $after, 'limit' => $limit, 'with_counts' => $withCounts],
            fn(mixed $value) => $value !== null
        );
        return $this->request('GET', 'users/@me/guilds', ['query' => $query]);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Internal
    ////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    private function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->httpClient->request($method, filter_var($url, FILTER_SANITIZE_URL), $options);
    }
}
