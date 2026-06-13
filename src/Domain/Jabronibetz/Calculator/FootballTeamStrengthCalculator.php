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

use App\Domain\Jabronibetz\DTO\FootballMatchTeamReferenceFrameAggregation;
use App\Domain\Jabronibetz\DTO\FootballTeamStrength;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class FootballTeamStrengthCalculator
{
    /**
     * @param FootballMatchTeamReferenceFrameAggregation[] $aggregations
     * @return array<string, FootballTeamStrength>
     */
    public function calculate(array $aggregations): array
    {
        $numTeams = count($aggregations);
        if ($numTeams === 0) {
            return [];
        }

        $totalGoalsForPerFulltime = 0;
        $totalGoalsAgainstPerFulltime = 0;
        foreach ($aggregations as $aggregation) {
            $totalGoalsForPerFulltime += $aggregation->goalsForPerFulltime;
            $totalGoalsAgainstPerFulltime += $aggregation->goalsAgainstPerFulltime;
        }
        $averageGoalsForPerFulltime = $totalGoalsForPerFulltime / $numTeams;
        $averageGoalsAgainstPerFulltime = $totalGoalsAgainstPerFulltime / $numTeams;

        $teamStrengths = [];
        foreach ($aggregations as $aggregation) {
            $teamStrengths[(string)$aggregation->teamId] = new FootballTeamStrength(
                teamId: $aggregation->teamId,
                attack: (float)(empty($averageGoalsForPerFulltime) ? 0 : ($aggregation->goalsForPerFulltime / $averageGoalsForPerFulltime)),
                defense: (float)(empty($averageGoalsAgainstPerFulltime) ? 0 : ($aggregation->goalsAgainstPerFulltime / $averageGoalsAgainstPerFulltime))
            );
        }
        return $teamStrengths;
    }
}
