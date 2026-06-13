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
use App\Domain\Jabronibetz\Entity\FootballMatchTeamReferenceFrame;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class FootballMatchTeamReferenceFrameAggregationCalculator
{
    /**
     * @param DenormalizerInterface $denormalizer
     */
    public function __construct(private DenormalizerInterface $denormalizer)
    {
    }

    /**
     * @param FootballMatchTeamReferenceFrame[] $matches
     * @return FootballMatchTeamReferenceFrameAggregation[]
     * @throws SerializerExceptionInterface
     */
    public function calculate(array $matches): array
    {
        $aggregations = [];
        foreach ($matches as $match) {
            $teamId = (string)$match->getTeam()->getId();

            if (!isset($aggregations[$teamId])) {
                $aggregations[$teamId] = [
                    'matchIds' => [],
                    'competitionIds' => [],

                    'teamId' => (int)$teamId,

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

                    'sequence' => []
                ];
            }

            $aggregations[$teamId]['matchIds'][] = $match->getSourceMatch()->getId();
            $aggregations[$teamId]['competitionIds'][] = $match->getCompetition()->getId();

            $aggregations[$teamId]['matches']++;

            if ($match->getHalftimeGoalsFor() !== null && $match->getHalftimeGoalsAgainst() !== null) {
                $aggregations[$teamId]['halftimesPlayed']++;
            }
            if ($match->getFulltimeGoalsFor() !== null && $match->getFulltimeGoalsAgainst() !== null) {
                $aggregations[$teamId]['fulltimesPlayed']++;
            }
            if ($match->getExtraHalftimeGoalsFor() !== null && $match->getExtraHalftimeGoalsAgainst() !== null) {
                $aggregations[$teamId]['extraHalftimesPlayed']++;
            }
            if ($match->getExtraFulltimeGoalsFor() !== null && $match->getExtraFulltimeGoalsAgainst() !== null) {
                $aggregations[$teamId]['extraFulltimesPlayed']++;
            }
            if ($match->getShootoutGoalsFor() !== null && $match->getShootoutGoalsAgainst() !== null) {
                $aggregations[$teamId]['shootoutsPlayed']++;
            }

            $halftimeGoalsFor = $match->getHalftimeGoalsFor();
            $fulltimeGoalsFor = $match->getFulltimeGoalsFor();
            $extraHalftimeGoalsFor = $match->getExtraHalftimeGoalsFor();
            $extraFulltimeGoalsFor = $match->getExtraFulltimeGoalsFor();
            $shootoutGoalsFor = $match->getShootoutGoalsFor();

            $aggregations[$teamId]['halftimeGoalsFor'] += $halftimeGoalsFor ?? 0;
            $aggregations[$teamId]['fulltimeGoalsFor'] += $fulltimeGoalsFor ?? 0;
            $aggregations[$teamId]['extraHalftimeGoalsFor'] += $extraHalftimeGoalsFor ?? 0;
            $aggregations[$teamId]['extraFulltimeGoalsFor'] += $extraFulltimeGoalsFor ?? 0;
            $aggregations[$teamId]['shootoutGoalsFor'] += $shootoutGoalsFor ?? 0;

            $halftimeGoalsAgainst = $match->getHalftimeGoalsAgainst();
            $fulltimeGoalsAgainst = $match->getFulltimeGoalsAgainst();
            $extraHalftimeGoalsAgainst = $match->getExtraHalftimeGoalsAgainst();
            $extraFulltimeGoalsAgainst = $match->getExtraFulltimeGoalsAgainst();
            $shootoutGoalsAgainst = $match->getShootoutGoalsAgainst();

            $aggregations[$teamId]['halftimeGoalsAgainst'] += $halftimeGoalsAgainst ?? 0;
            $aggregations[$teamId]['fulltimeGoalsAgainst'] += $fulltimeGoalsAgainst ?? 0;
            $aggregations[$teamId]['extraHalftimeGoalsAgainst'] += $extraHalftimeGoalsAgainst ?? 0;
            $aggregations[$teamId]['extraFulltimeGoalsAgainst'] += $extraFulltimeGoalsAgainst ?? 0;
            $aggregations[$teamId]['shootoutGoalsAgainst'] += $shootoutGoalsAgainst ?? 0;

            if ($shootoutGoalsFor !== null && $shootoutGoalsAgainst !== null) {
                $aggregations[$teamId]['sequence'][] = $shootoutGoalsFor > $shootoutGoalsAgainst
                    ? 'W'
                    : ($shootoutGoalsFor < $shootoutGoalsAgainst ? 'L' : 'D');
            } else if ($extraFulltimeGoalsFor !== null && $extraFulltimeGoalsAgainst !== null) {
                $aggregations[$teamId]['sequence'][] = $extraFulltimeGoalsFor > $extraFulltimeGoalsAgainst
                    ? 'W'
                    : ($extraFulltimeGoalsFor < $extraFulltimeGoalsAgainst ? 'L' : 'D');
            } else if ($extraHalftimeGoalsFor !== null && $extraHalftimeGoalsAgainst !== null) {
                $aggregations[$teamId]['sequence'][] = $extraHalftimeGoalsFor > $extraHalftimeGoalsAgainst
                    ? 'W'
                    : ($extraHalftimeGoalsFor < $extraHalftimeGoalsAgainst ? 'L' : 'D');
            }else if ($fulltimeGoalsFor !== null && $fulltimeGoalsAgainst !== null) {
                $aggregations[$teamId]['sequence'][] = $fulltimeGoalsFor > $fulltimeGoalsAgainst
                    ? 'W'
                    : ($fulltimeGoalsFor < $fulltimeGoalsAgainst ? 'L' : 'D');
            } else if ($halftimeGoalsFor !== null && $halftimeGoalsAgainst !== null) {
                $aggregations[$teamId]['sequence'][] = $halftimeGoalsFor > $halftimeGoalsAgainst
                    ? 'W'
                    : ($halftimeGoalsFor < $halftimeGoalsAgainst ? 'L' : 'D');
            } else {
                $aggregations[$teamId]['sequence'][] = '-';
            }
        }

        foreach ($aggregations as &$aggregation) {
            $aggregation['goalsForPerHalftime'] = (float)($aggregation['halftimesPlayed'] === 0 ? 0 : ($aggregation['halftimeGoalsFor'] / $aggregation['halftimesPlayed']));
            $aggregation['goalsForPerFulltime'] = (float)($aggregation['fulltimesPlayed'] === 0 ? 0 : ($aggregation['fulltimeGoalsFor'] / $aggregation['fulltimesPlayed']));
            $aggregation['goalsForPerExtraHalftime'] = (float)($aggregation['extraHalftimesPlayed'] === 0 ? 0 : ($aggregation['extraHalftimeGoalsFor'] / $aggregation['extraHalftimesPlayed']));
            $aggregation['goalsForPerExtraFulltime'] = (float)($aggregation['extraFulltimesPlayed'] === 0 ? 0 : ($aggregation['extraFulltimeGoalsFor'] / $aggregation['extraFulltimesPlayed']));
            $aggregation['goalsForPerShootout'] = (float)($aggregation['shootoutsPlayed'] === 0 ? 0 : ($aggregation['shootoutGoalsFor'] / $aggregation['shootoutsPlayed']));

            $aggregation['goalsAgainstPerHalftime'] = (float)($aggregation['halftimesPlayed'] === 0 ? 0 : ($aggregation['halftimeGoalsAgainst'] / $aggregation['halftimesPlayed']));
            $aggregation['goalsAgainstPerFulltime'] = (float)($aggregation['fulltimesPlayed'] === 0 ? 0 : ($aggregation['fulltimeGoalsAgainst'] / $aggregation['fulltimesPlayed']));
            $aggregation['goalsAgainstPerExtraHalftime'] = (float)($aggregation['extraHalftimesPlayed'] === 0 ? 0 : ($aggregation['extraHalftimeGoalsAgainst'] / $aggregation['extraHalftimesPlayed']));
            $aggregation['goalsAgainstPerExtraFulltime'] = (float)($aggregation['extraFulltimesPlayed'] === 0 ? 0 : ($aggregation['extraFulltimeGoalsAgainst'] / $aggregation['extraFulltimesPlayed']));
            $aggregation['goalsAgainstPerShootout'] = (float)($aggregation['shootoutsPlayed'] === 0 ? 0 : ($aggregation['shootoutGoalsAgainst'] / $aggregation['shootoutsPlayed']));

            $aggregation['halftimeGoalDifference'] = $aggregation['halftimeGoalsFor'] - $aggregation['halftimeGoalsAgainst'];
            $aggregation['fulltimeGoalDifference'] = $aggregation['fulltimeGoalsFor'] - $aggregation['fulltimeGoalsAgainst'];
            $aggregation['extraHalftimeGoalDifference'] = $aggregation['extraHalftimeGoalsFor'] - $aggregation['extraHalftimeGoalsAgainst'];
            $aggregation['extraFulltimeGoalDifference'] = $aggregation['extraFulltimeGoalsFor'] - $aggregation['extraFulltimeGoalsAgainst'];
            $aggregation['shootoutGoalDifference'] = $aggregation['shootoutGoalsFor'] - $aggregation['shootoutGoalsAgainst'];
        }

        return $this->denormalizer->denormalize(array_values($aggregations), FootballMatchTeamReferenceFrameAggregation::class . '[]');
    }
}
