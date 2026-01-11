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

use function Symfony\Component\String\s;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class MatchTable extends AbstractTable
{
    public const string BASE_NAME = 'match';

    public static function getCreateSql(string $nation, string $competition, string $season): string
    {
        $sql = <<<'SQL'
            CREATE TABLE <table_name> (
                home_team_name TEXT NOT NULL,
                away_team_name TEXT NOT NULL,
                home_team_score SMALLINT DEFAULT NULL,
                away_team_score SMALLINT DEFAULT NULL,
                timestamp BIGINT DEFAULT NULL,
                extra TEXT DEFAULT NULL,
                PRIMARY KEY (home_team_name, away_team_name)
            );
SQL;

        return s($sql)
            ->replace('<table_name>', self::getName($nation, $competition, $season))
            ->toString();
    }
}
