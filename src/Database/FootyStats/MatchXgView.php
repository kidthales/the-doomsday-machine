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

namespace App\Database\FootyStats;

use App\Entity\FootyStats\Target;
use function Symfony\Component\String\s;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class MatchXgView extends AbstractView
{
    public const string BASE_NAME = 'match_xg';

    public static function getCreateSql(Target $target): string
    {
        $sql = <<<'SQL'
            CREATE VIEW <view_name> AS
            SELECT home_team_name,
                   away_team_name,
                   (SELECT AVG(goals_for_per_game) FROM <home_team_standing_view_name>) * h.attack * a.defense AS home_team_xg,
                   (SELECT AVG(goals_for_per_game) FROM <away_team_standing_view_name>) * a.attack * h.defense AS away_team_xg,
                   timestamp,
                   extra
            FROM <match_table_name> AS m
                INNER JOIN <team_strength_view_name> AS h ON m.home_team_name = h.team_name
                INNER JOIN <team_strength_view_name> AS a ON m.away_team_name = a.team_name
            WHERE m.home_team_score IS NULL
            ORDER BY timestamp;
SQL;

        return s($sql)
            ->replace('<view_name>', self::getName($target))
            ->replace('<home_team_standing_view_name>', HomeTeamStandingView::getName($target))
            ->replace('<away_team_standing_view_name>', AwayTeamStandingView::getName($target))
            ->replace('<match_table_name>', MatchTable::getName($target))
            ->replace('<team_strength_view_name>', TeamStrengthView::getName($target))
            ->toString();
    }
}
