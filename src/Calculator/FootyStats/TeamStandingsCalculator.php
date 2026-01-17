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

namespace App\Calculator\FootyStats;

use Random\Randomizer;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class TeamStandingsCalculator
{
    private Randomizer $randomizer;

    public function __construct()
    {
        $this->randomizer = new Randomizer();
    }

    public function calculate(array $matches, array $initialTeamStandings = []): array
    {
        $teamStandingsIndex = [];

        foreach ($initialTeamStandings as $initialTeamStanding) {
            $teamStandingsIndex[$initialTeamStanding['team_name']] = [
                'matches_played' => $initialTeamStanding['matches_played'],
                'wins' => $initialTeamStanding['wins'],
                'draws' => $initialTeamStanding['draws'],
                'losses' => $initialTeamStanding['losses'],
                'goals_for' => $initialTeamStanding['goals_for'],
                'goals_against' => $initialTeamStanding['goals_against'],
                'points' => $initialTeamStanding['points'],
                'sequence' => $initialTeamStanding['sequence']
            ];
        }

        foreach ($matches as $match) {
            $homeTeamName = $match['home_team_name'];
            $awayTeamName = $match['away_team_name'];

            if (!isset($teamStandingsIndex[$homeTeamName])) {
                $teamStandingsIndex[$homeTeamName] = [
                    'matches_played' => 0,
                    'wins' => 0,
                    'draws' => 0,
                    'losses' => 0,
                    'goals_for' => 0,
                    'goals_against' => 0,
                    'points' => 0,
                    'sequence' => ''
                ];
            }

            if (!isset($teamStandingsIndex[$awayTeamName])) {
                $teamStandingsIndex[$awayTeamName] = [
                    'matches_played' => 0,
                    'wins' => 0,
                    'draws' => 0,
                    'losses' => 0,
                    'goals_for' => 0,
                    'goals_against' => 0,
                    'points' => 0,
                    'sequence' => ''
                ];
            }

            $homeTeamScore = $match['home_team_score'];
            $awayTeamScore = $match['away_team_score'];

            ++$teamStandingsIndex[$homeTeamName]['matches_played'];
            $teamStandingsIndex[$homeTeamName]['goals_for'] += $homeTeamScore;
            $teamStandingsIndex[$homeTeamName]['goals_against'] += $awayTeamScore;

            ++$teamStandingsIndex[$awayTeamName]['matches_played'];
            $teamStandingsIndex[$awayTeamName]['goals_for'] += $awayTeamScore;
            $teamStandingsIndex[$awayTeamName]['goals_against'] += $homeTeamScore;

            if ($homeTeamScore > $awayTeamScore) {
                ++$teamStandingsIndex[$homeTeamName]['wins'];
                $teamStandingsIndex[$homeTeamName]['points'] += 3;
                $teamStandingsIndex[$homeTeamName]['sequence'] .= 'W';

                ++$teamStandingsIndex[$awayTeamName]['losses'];
                $teamStandingsIndex[$awayTeamName]['sequence'] .= 'L';
            } else if ($homeTeamScore === $awayTeamScore) {
                ++$teamStandingsIndex[$homeTeamName]['draws'];
                ++$teamStandingsIndex[$homeTeamName]['points'];
                $teamStandingsIndex[$homeTeamName]['sequence'] .= 'D';

                ++$teamStandingsIndex[$awayTeamName]['draws'];
                ++$teamStandingsIndex[$awayTeamName]['points'];
                $teamStandingsIndex[$awayTeamName]['sequence'] .= 'D';
            } else {
                ++$teamStandingsIndex[$homeTeamName]['losses'];
                $teamStandingsIndex[$homeTeamName]['sequence'] .= 'L';

                ++$teamStandingsIndex[$awayTeamName]['wins'];
                $teamStandingsIndex[$awayTeamName]['points'] += 3;
                $teamStandingsIndex[$awayTeamName]['sequence'] .= 'W';
            }
        }

        $teamStandings = [];

        foreach ($teamStandingsIndex as $teamName => $teamStanding) {
            $teamStandings[] = [
                'team_name' => $teamName,
                'matches_played' => $teamStanding['matches_played'],
                'wins' => $teamStanding['wins'],
                'draws' => $teamStanding['draws'],
                'losses' => $teamStanding['losses'],
                'goals_for' => $teamStanding['goals_for'],
                'goals_for_per_game' => $teamStanding['goals_for'] / $teamStanding['matches_played'],
                'goals_against' => $teamStanding['goals_against'],
                'goals_against_per_game' => $teamStanding['goals_against'] / $teamStanding['matches_played'],
                'goal_difference' => $teamStanding['goals_for'] - $teamStanding['goals_against'],
                'points' => $teamStanding['points'],
                'points_per_game' => $teamStanding['points'] / $teamStanding['matches_played'],
                'sequence' => $teamStanding['sequence']
            ];
        }

        usort($teamStandings, function (array $a, array $b) {
            if ($a['points'] === $b['points']) {
                if ($a['goal_difference'] === $b['goal_difference']) {
                    if ($a['goals_for'] === $b['goals_for']) {
                        return $this->randomizer->nextFloat() < 0.5 ? -1 : 1;
                    }

                    return $b['goals_for'] - $a['goals_for'];
                }

                return $b['goal_difference'] - $a['goal_difference'];
            }

            return $b['points'] - $a['points'];
        });

        return array_map(
            fn(array $teamStanding, int $ix) => ['position' => $ix + 1, ...$teamStanding],
            $teamStandings,
            array_keys($teamStandings)
        );
    }
}
