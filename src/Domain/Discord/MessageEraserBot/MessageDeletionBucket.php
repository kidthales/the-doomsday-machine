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

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class MessageDeletionBucket
{
    /**
     * @param array<array<array<string, mixed>>> $bulk
     * @param array<array<string, mixed>> $individual
     */
    public function __construct(public array $bulk, public array $individual)
    {
    }

    /**
     * @return int
     */
    public function getBulkCount(): int
    {
        return array_reduce($this->bulk, fn (int $c, array $a) => $c + count($a), 0);
    }

    /**
     * @return int
     */
    public function getIndividualCount(): int
    {
        return count($this->individual);
    }
}
