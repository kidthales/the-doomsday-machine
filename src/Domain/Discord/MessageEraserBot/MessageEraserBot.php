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

namespace App\Domain\Discord\MessageEraserBot;

use App\Domain\Shared\Discord\DiscordBot;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class MessageEraserBot extends DiscordBot
{
    // Get Channel Messages: VIEW_CHANNEL, CONNECT (voice), READ_MESSAGE_HISTORY
    // Delete Message: MANAGE_MESSAGES
    // Bulk Delete Messages: MANAGE_MESSAGES
    // Delete Thread: MANAGE_THREADS
    protected const int DISCORD_PERMISSIONS = 17180992512;

    /**
     * @param string $token
     */
    public function __construct(#[Autowire(env: 'DISCORD_MESSAGE_ERASER_BOT_TOKEN')] string $token)
    {
        parent::__construct($token);
    }

    // TODO
}
