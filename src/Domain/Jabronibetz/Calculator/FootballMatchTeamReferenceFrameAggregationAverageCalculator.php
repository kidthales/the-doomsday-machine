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
use App\Domain\Jabronibetz\DTO\FootballMatchTeamReferenceFrameAggregationAverage;
use App\Domain\Jabronibetz\Entity\FootballMatchTeamReferenceFrame;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class FootballMatchTeamReferenceFrameAggregationAverageCalculator
{
    /**
     * @param DenormalizerInterface $denormalizer
     */
    public function __construct(private DenormalizerInterface $denormalizer)
    {
    }

    /**
     * @param FootballMatchTeamReferenceFrameAggregation[] $aggregations
     * @return FootballMatchTeamReferenceFrameAggregationAverage
     * @throws SerializerExceptionInterface
     */
    public function calculate(array $aggregations): FootballMatchTeamReferenceFrameAggregationAverage
    {
        $matchIds = [];
        $competitionIds = [];
        $teamIds = [];
        $totals = [
            'matches' => 0,

            'halftimesPlayed' => 0,
            'fulltimesPlayed' => 0,
            'extraHalftimesPlayed' => 0,
            'extraFulltimesPlayed' => 0,
            'shootoutsPlayed' => 0,

            'halftimeGoalsFor' => 0,
            'fulltimeGoalsFor' => 0,
            'extraHalftimeGoalsFor' => 0,
            'extraFulltimeGoalsFor' => 0,
            'shootoutGoalsFor' => 0,

            'halftimeGoalsAgainst' => 0,
            'fulltimeGoalsAgainst' => 0,
            'extraHalftimeGoalsAgainst' => 0,
            'extraFulltimeGoalsAgainst' => 0,
            'shootoutGoalsAgainst' => 0,

            'goalsForPerHalftime' => 0.0,
            'goalsForPerFulltime' => 0.0,
            'goalsForPerExtraHalftime' => 0.0,
            'goalsForPerExtraFulltime' => 0.0,
            'goalsForPerShootout' => 0.0,

            'goalsAgainstPerHalftime' => 0.0,
            'goalsAgainstPerFulltime' => 0.0,
            'goalsAgainstPerExtraHalftime' => 0.0,
            'goalsAgainstPerExtraFulltime' => 0.0,
            'goalsAgainstPerShootout' => 0.0,

            'halftimeGoalDifference' => 0,
            'fulltimeGoalDifference' => 0,
            'extraHalftimeGoalDifference' => 0,
            'extraFulltimeGoalDifference' => 0,
            'shootoutGoalDifference' => 0,
        ];
        $fields = array_keys($totals);
        foreach ($aggregations as $aggregation) {
            $matchIds = [...$matchIds, ...$aggregation->matchIds];
            $competitionIds = [...$competitionIds, ...$aggregation->competitionIds];
            $teamIds = [...$teamIds, $aggregation->teamId];

            foreach ($fields as $field) {
                $totals[$field] += $aggregation->$field;
            }
        }

        $numTeams = count($teamIds);
        $data = [
            'matchIds' => array_unique($matchIds),
            'competitionIds' => array_unique($competitionIds),
            'teamIds' => $teamIds
        ];
        foreach ($totals as $field => $total) {
            $data[$field] = (float)($numTeams === 0 ? 0.0 : ($total / $numTeams));
        }

        return $this->denormalizer->denormalize($data, FootballMatchTeamReferenceFrameAggregationAverage::class);
    }
}
