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
     * @return FootballTeamStrength[]
     */
    public function calculate(array $aggregations): array
    {
        $totalGoalsFor = 0;
        $totalGoalsAgainst = 0;

        foreach ($aggregations as $aggregation) {
            $totalGoalsFor += $aggregation->fulltimeGoalsFor;
            $totalGoalsAgainst += $aggregation->fulltimeGoalsAgainst;
        }

        $numTeams = count($aggregations);
        $averageGoalsFor = $numTeams === 0 ? 0 : ($totalGoalsFor / $numTeams);
        $averageGoalsAgainst = $numTeams === 0 ? 0 : ($totalGoalsAgainst / $numTeams);

        $teamStrengths = [];

        foreach ($aggregations as $aggregation) {
            $teamStrengths[] = new FootballTeamStrength(
                teamId: $aggregation->teamId,
                attack: $averageGoalsFor === 0 ? 0 : ($aggregation->fulltimeGoalsFor / $averageGoalsFor),
                defense: $averageGoalsAgainst === 0 ? 0 : ($aggregation->fulltimeGoalsAgainst / $averageGoalsAgainst)
            );
        }

        return $teamStrengths;
    }
}
