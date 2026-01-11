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
abstract readonly class AbstractTeamStandingView extends AbstractView
{
    protected const ?string CREATE_SQL_TEMPLATE = null;

    public static function getCreateSql(string $nation, string $competition, string $season): string
    {
        if (static::CREATE_SQL_TEMPLATE === null) {
            throw new LogicException('CREATE_SQL_TEMPLATE is not defined');
        }

        return s(static::CREATE_SQL_TEMPLATE)
            ->replace('<view_name>', static::getName($nation, $competition, $season))
            ->replace('<match_table_name>', MatchTable::getName($nation, $competition, $season))
            ->toString();
    }
}
