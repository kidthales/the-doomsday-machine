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
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class FootballTeamStrengthCalculator
{
    use FootballMatchTeamReferenceFrameAggregationAverageCalculatorAwareTrait;

    /**
     * @param FootballMatchTeamReferenceFrameAggregation[] $aggregations
     * @return array<string, FootballTeamStrength>
     * @throws SerializerExceptionInterface
     */
    public function calculate(array $aggregations): array
    {
        $numTeams = count($aggregations);
        if ($numTeams === 0) {
            return [];
        }

        $aggregationAverage = $this->footballMatchTeamReferenceFrameAggregationAverageCalculator->calculate($aggregations);

        $teamStrengths = [];
        foreach ($aggregations as $aggregation) {
            $teamStrengths[(string)$aggregation->teamId] = new FootballTeamStrength(
                teamId: $aggregation->teamId,
                attack: (float)(
                    empty($aggregationAverage->goalsForPerFulltime)
                        ? 0.0
                        : ($aggregation->goalsForPerFulltime / $aggregationAverage->goalsForPerFulltime)
                ),
                defense: (float)(
                    empty($aggregationAverage->goalsAgainstPerFulltime)
                        ? 0.0
                        : ($aggregation->goalsAgainstPerFulltime / $aggregationAverage->goalsAgainstPerFulltime)
                )
            );
        }
        return $teamStrengths;
    }
}
