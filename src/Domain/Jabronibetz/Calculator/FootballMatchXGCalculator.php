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

namespace App\Domain\Jabronibetz\Calculator;

use App\Domain\Jabronibetz\DTO\FootballMatchXG;
use App\Domain\Jabronibetz\DTO\FootballTeamStrength;
use App\Domain\Jabronibetz\Entity\FootballMatch;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class FootballMatchXGCalculator
{
    /**
     * @param FootballMatch[] $matches
     * @param float|float[] $competitionAverageGoalsForPerFulltime
     * @param array<string, FootballTeamStrength> $teamStrengths
     * @return array<string, FootballMatchXG>
     */
    public function calculate(array $matches, float|array $competitionAverageGoalsForPerFulltime, array $teamStrengths): array
    {
        if (count($matches) === 0) {
            return [];
        }

        if (is_float($competitionAverageGoalsForPerFulltime)) {
            $competitionAverageGoalsForPerFulltime = array_fill(0, 2, $competitionAverageGoalsForPerFulltime);
        } else {
            $competitionAverageGoalsForPerFulltimeCount = count($competitionAverageGoalsForPerFulltime);
            switch ($competitionAverageGoalsForPerFulltimeCount) {
                case 0:
                    return [];
                case 1:
                    $competitionAverageGoalsForPerFulltime[] = $competitionAverageGoalsForPerFulltime[0];
                    break;
                default:
                    break;
            }
        }

        if (count($teamStrengths) === 0) {
            return [];
        }

        $matchXGs = [];
        foreach ($matches as $match) {
            $matchId = $match->getId();
            $homeTeamId = $match->getHomeTeam()->getId();
            $awayTeamId = $match->getAwayTeam()->getId();

            if ($matchId === null || $homeTeamId === null || $awayTeamId === null) {
                continue;
            }

            $homeTeamStrength = $teamStrengths[$homeTeamId] ?? null;
            $awayTeamStrength = $teamStrengths[$awayTeamId] ?? null;

            if ($homeTeamStrength === null || $awayTeamStrength === null) {
                continue;
            }

            $matchXGs[(string)$matchId] = new FootballMatchXG(
                matchId: $matchId,
                homeTeam: $competitionAverageGoalsForPerFulltime[0] * $homeTeamStrength->attack * $awayTeamStrength->defense,
                awayTeam: $competitionAverageGoalsForPerFulltime[1] * $awayTeamStrength->attack * $homeTeamStrength->defense,
            );
        }

        return $matchXGs;
    }
}
