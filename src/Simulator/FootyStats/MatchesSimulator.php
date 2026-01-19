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

use App\Calculator\FootyStats\MatchChancesCalculatorAwareTrait;
use App\Database\FootyStats\MatchTableAwareTrait;
use App\Entity\FootyStats\Target;
use Doctrine\DBAL\Exception as DBALException;
use Random\Randomizer;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class MatchesSimulator
{
    use MatchChancesCalculatorAwareTrait, MatchTableAwareTrait;

    private readonly Randomizer $randomizer;

    public function __construct()
    {
        $this->randomizer = new Randomizer();
    }

    /**
     * @param Target $target
     * @param int $numRuns
     * @param callable $callback
     * @return void
     * @throws DBALException
     */
    public function simulate(Target $target, int $numRuns, callable $callback): void
    {
        $matchChances = $this->matchChancesCalculator->calculate($target);

        $pendingMatches = $this->footyStatsMatchTable
            ->createSelectQueryBuilder($target)
            ->select('*')
            ->where('home_team_score IS NULL')
            ->orderBy('timestamp')
            ->fetchAllAssociative();

        for ($i = 0; $i < $numRuns; ++$i) {
            $simulatedMatches = [];

            foreach ($pendingMatches as $pendingMatch) {
                $homeTeamName = $pendingMatch['home_team_name'];
                $awayTeamName = $pendingMatch['away_team_name'];

                $simulatedMatchResult = $this->simulateMatch(...$matchChances[$homeTeamName][$awayTeamName]);

                $simulatedMatches[] = [
                    'home_team_name' => $homeTeamName,
                    'away_team_name' => $awayTeamName,
                    'home_team_score' => $simulatedMatchResult['home_team_score'],
                    'away_team_score' => $simulatedMatchResult['away_team_score'],
                    'timestamp' => $pendingMatch['timestamp'],
                    'extra' => $pendingMatch['extra'],
                ];
            }

            $callback($simulatedMatches, $i + 1, $this);
        }
    }

    private function simulateMatch(
        array $homeTeamWinProbabilities,
        array $drawProbabilities,
        array $awayTeamWinProbabilities
    ): array
    {
        $totalHomeTeamWinProbability = array_sum(array_values($homeTeamWinProbabilities));
        $totalDrawProbability = array_sum(array_values($drawProbabilities));

        $result = $this->randomizer->nextFloat();

        if ($result < $totalHomeTeamWinProbability) {
            $finalScore = $this->chooseScore($homeTeamWinProbabilities);
        } else if ($result < $totalHomeTeamWinProbability + $totalDrawProbability) {
            $finalScore = $this->chooseScore($drawProbabilities);
        } else {
            $finalScore = $this->chooseScore($awayTeamWinProbabilities);
        }

        return array_combine(['home_team_score', 'away_team_score'], explode('-', $finalScore));
    }

    private function chooseScore(array $scoreProbabilities): string
    {
        $scoreProbabilitiesTotal = array_sum(array_values($scoreProbabilities));
        $accumulator = 0;

        $result = $this->randomizer->nextFloat();

        foreach ($scoreProbabilities as $score => $probability) {
            if ($result < ($accumulator += ($probability / $scoreProbabilitiesTotal))) {
                return $score;
            }
        }

        // Fallback
        return array_rand($scoreProbabilities);
    }
}
