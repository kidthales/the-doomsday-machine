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

namespace App\FootyStats\Database;

use LogicException;
use function Symfony\Component\String\s;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
abstract readonly class AbstractTableOrView
{
    /**
     * Define in concrete child class implementations.
     */
    public const ?string BASE_NAME = null;

    /**
     * Get the table or view name for the specified nation/competition/season combination.
     *
     * @param string $nation
     * @param string $competition
     * @param string $season
     * @return string
     */
    final public static function getName(string $nation, string $competition, string $season): string
    {
        if (static::BASE_NAME === null) {
            throw new LogicException('BASE_NAME is not defined');
        }

        return sprintf(
            '%s_%s_%s_%s',
            s($nation)->snake()->toString(),
            s($competition)->snake()->toString(),
            s($season)->snake()->toString(),
            s(static::BASE_NAME)->snake()->toString()
        );
    }

    abstract public static function getCreateSql(string $nation, string $competition, string $season): string;

    abstract public static function getDropSql(string $nation, string $competition, string $season): string;
}
