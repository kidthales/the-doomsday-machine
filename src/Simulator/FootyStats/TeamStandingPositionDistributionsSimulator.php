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

namespace App\Simulator\FootyStats;

use App\Calculator\FootyStats\TeamStandingsCalculatorAwareTrait;
use App\Database\FootyStats\TeamStandingViewAwareTrait;
use App\Entity\FootyStats\Target;
use Doctrine\DBAL\Exception as DBALException;
use NumberFormatter;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class TeamStandingPositionDistributionsSimulator
{
    use TeamStandingsCalculatorAwareTrait, TeamStandingViewAwareTrait;

    private readonly NumberFormatter $ordinalNumberFormatter;

    public function __construct(private readonly MatchesSimulator $matchesSimulator)
    {
        $this->ordinalNumberFormatter = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
    }

    /**
     * @param Target $target
     * @param int $numRuns
     * @param callable $callback
     * @return array
     * @throws DBALException
     */
    public function simulate(Target $target, int $numRuns, callable $callback): array
    {
        $initialTeamStandings = $this->teamStandingView
            ->createSelectQueryBuilder($target)
            ->select('*')
            ->fetchAllAssociative();

        $teamStandingsPositionCounts = [];

        $this->matchesSimulator->simulate(
            $target,
            $numRuns,
            function (array $simulatedMatches, int $run) use (&$teamStandingsPositionCounts, $initialTeamStandings, $callback) {
                $teamStandings = $this->teamStandingsCalculator->calculate($simulatedMatches, $initialTeamStandings);

                foreach ($teamStandings as $teamStanding) {
                    $teamName = $teamStanding['team_name'];

                    if (!isset($teamStandingsPositionCounts[$teamName])) {
                        $teamStandingsPositionCounts[$teamName] = array_fill(0, count($teamStandings), 0);
                    }

                    ++$teamStandingsPositionCounts[$teamName][$teamStanding['position'] - 1];
                }

                $callback($teamStandingsPositionCounts, $run, $this);
            }
        );

        $teamStandingsPositionDistributions = [];

        foreach ($teamStandingsPositionCounts as $teamName => $teamStandingPositionCounts) {
            $teamStandingPositionDistribution = ['team_name' => $teamName];

            for ($i = 0; $i < count($teamStandingPositionCounts); ++$i) {
                $position = $this->ordinalNumberFormatter->format($i + 1);
                $teamStandingPositionDistribution[$position] = $teamStandingPositionCounts[$i] / $numRuns;
            }

            $teamStandingsPositionDistributions[] = $teamStandingPositionDistribution;
        }

        usort(
            $teamStandingsPositionDistributions,
            fn(array $a, array $b) => strcmp($a['team_name'], $b['team_name'])
        );

        return $teamStandingsPositionDistributions;
    }
}
