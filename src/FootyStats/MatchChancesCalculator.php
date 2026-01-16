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

namespace App\FootyStats;

use App\FootyStats\Database\MatchXgView;
use Doctrine\DBAL\Exception as DBALException;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class MatchChancesCalculator
{
    private const int MAX_TEAM_GOALS = 10;

    private array $factorials = [];

    public function __construct(private readonly MatchXgView $matchXgView)
    {
    }

    /**
     * @param Target $target
     * @return array
     * @throws DBALException
     */
    public function calculate(Target $target): array
    {
        $matchXgAll = $this->matchXgView
            ->createSelectQueryBuilder($target)
            ->select('*')
            ->fetchAllAssociative();

        $matchChances = [];

        foreach ($matchXgAll as $matchXg) {
            $homeTeamGoalChances = [];
            $awayTeamGoalChances = [];
            $drawChances = [];

            for ($k = 0; $k <= self::MAX_TEAM_GOALS; ++$k) {
                $homeTeamGoalChances[] = $this->poisson($k, $matchXg['home_team_xg']);
                $awayTeamGoalChances[] = $this->poisson($k, $matchXg['away_team_xg']);
                $drawChances["$k-$k"] = $homeTeamGoalChances[$k] * $awayTeamGoalChances[$k];
            }

            $homeWinChances = [];
            $awayWinChances = [];

            for ($w = 1; $w <= self::MAX_TEAM_GOALS; ++$w) {
                for ($l = 0; $l < $w; ++$l) {
                    $homeWinChances["$w-$l"] = $homeTeamGoalChances[$w] * $awayTeamGoalChances[$l];
                    $awayWinChances["$l-$w"] = $homeTeamGoalChances[$l] * $awayTeamGoalChances[$w];
                }
            }

            $homeTeamName = $matchXg['home_team_name'];

            if (!isset($matchChances[$homeTeamName])) {
                $matchChances[$homeTeamName] = [];
            }

            $matchChances[$homeTeamName][$matchXg['away_team_name']] = [
                $homeWinChances,
                $drawChances,
                $awayWinChances
            ];
        }

        return $matchChances;
    }

    private function poisson(int $k, float $lambda): float
    {
        return (pow($lambda, $k) * pow(M_E, -$lambda)) / $this->factorial($k);
    }

    private function factorial(int $n): int
    {
        if (isset($this->factorials[$n])) {
            return $this->factorials[$n];
        }

        if ($n < 2) {
            return $this->factorials[$n] = 1;
        }

        $f = 1;
        for ($k = 2; $k <= $n; ++$k) {
            $f *= $k;
        }

        return $this->factorials[$n] = $f;
    }
}
