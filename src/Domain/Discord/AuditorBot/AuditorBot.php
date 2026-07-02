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

namespace App\Domain\Discord\AuditorBot;

use App\Domain\Shared\Discord\DiscordBot;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class AuditorBot extends DiscordBot
{
    // Get Guild Audit Log: VIEW_AUDIT_LOG
    protected const int DISCORD_PERMISSIONS = 128;

    /**
     * @param string $token
     */
    public function __construct(#[Autowire(env: 'string:DISCORD_AUDITOR_BOT_TOKEN')] string $token)
    {
        parent::__construct($token);
    }

    /**
     * @param string $guildId
     * @param string|null $userId
     * @param int|null $actionType
     * @param string|null $before
     * @param string|null $after
     * @param int|null $limit
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getGuildAuditLogEntries(
        string  $guildId,
        ?string $userId = null,
        ?int    $actionType = null,
        ?string $before = null,
        ?string $after = null,
        ?int    $limit = null
    ): array
    {
        return $this->discordApi
            ->getGuildAuditLog($guildId, $userId, $actionType, $before, $after, $limit)
            ->toArray()['audit_log_entries'];
    }
}
