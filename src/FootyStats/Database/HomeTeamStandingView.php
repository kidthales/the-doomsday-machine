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

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Autoconfigure(public: true)]
final readonly class HomeTeamStandingView extends AbstractTeamStandingView
{
    public const string BASE_NAME = 'home_team_standing';

    protected const string CREATE_SQL_TEMPLATE = <<<'SQL'
            CREATE VIEW <view_name> AS
            SELECT ROW_NUMBER() OVER (ORDER BY 1) AS position, *
            FROM(SELECT team_name,
                        COUNT(team_name)                                                                                 AS matches_played,
                        SUM(CASE WHEN goals_for > goals_against THEN 1 ELSE 0 END)                                       AS wins,
                        SUM(CASE WHEN goals_for = goals_against THEN 1 ELSE 0 END)                                       AS draws,
                        SUM(CASE WHEN goals_for < goals_against THEN 1 ELSE 0 END)                                       AS losses,
                        SUM(goals_for)                                                                                   AS goals_for,
                        SUM(goals_for) / CAST(COUNT(team_name) AS FLOAT)                                                 AS goals_for_per_game,
                        SUM(goals_against)                                                                               AS goals_against,
                        SUM(goals_against) / CAST(COUNT(team_name) AS FLOAT)                                             AS goals_against_per_game,
                        SUM(goals_for) - SUM(goals_against)                                                              AS goal_difference,
                        SUM(CASE WHEN goals_for > goals_against THEN 3 WHEN goals_for = goals_against THEN 1 ELSE 0 END) AS points,
                        SUM(CASE WHEN goals_for > goals_against THEN 3 WHEN goals_for = goals_against THEN 1 ELSE 0 END) /
                        CAST(COUNT(team_name) AS FLOAT)                                                                  AS points_per_game,
                        GROUP_CONCAT(
                                CASE WHEN goals_for > goals_against THEN 'W' WHEN goals_for = goals_against THEN 'D' ELSE 'L' END,
                                '')                                                                                 AS sequence
                  FROM (SELECT *
                        FROM (SELECT home_team_name  AS team_name,
                                     home_team_score AS goals_for,
                                     away_team_score AS goals_against,
                                     timestamp
                              FROM <match_table_name>
                              WHERE home_team_score IS NOT NULL)
                        ORDER BY timestamp)
                  GROUP BY team_name
                  ORDER BY points DESC, goal_difference DESC, goals_for DESC, team_name);
SQL;
}
