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

namespace App\Console\Command\FootyStats;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
trait PrettyTeamStandingsTrait
{
    protected static function prettifyTeamStandings(array $teamStandings): array
    {
        return array_map(
            fn (array $teamStanding) => [
                '#' => $teamStanding['position'],
                'Team' => $teamStanding['team_name'],
                'MP' => $teamStanding['matches_played'],
                'W' => $teamStanding['wins'],
                'D' => $teamStanding['draws'],
                'L' => $teamStanding['losses'],
                'GF' => $teamStanding['goals_for'],
                'GA' => $teamStanding['goals_against'],
                'GD' => $teamStanding['goal_difference'],
                'Pts' => $teamStanding['points'],
                'Last 5' => substr($teamStanding['sequence'], -5),
                'PPG' => number_format(round($teamStanding['points_per_game'], 2), 2)
            ],
            $teamStandings
        );
    }
}
