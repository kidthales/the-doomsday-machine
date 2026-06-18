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

use App\Domain\Jabronibetz\DTO\FootballCompetitionAverageGoalsForPerFulltime;
use App\Domain\Jabronibetz\DTO\FootballMatchTeamReferenceFrameAggregation;
use App\Domain\Jabronibetz\DTO\FootballMatchTeamReferenceFrameAggregationAverage;
use App\Domain\Jabronibetz\DTO\FootballMatchXG;
use App\Domain\Jabronibetz\DTO\FootballTeamStrength;
use App\Domain\Jabronibetz\Entity\FootballCompetitionTeamEntry;
use App\Domain\Jabronibetz\Entity\FootballMatch;
use App\Domain\Jabronibetz\Entity\FootballMatchTeamReferenceFrame;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class FootballCalculator
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
    public function calculateMatchTeamReferenceFrameAggregations(array $matches): array
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
            } else if ($fulltimeGoalsFor !== null && $fulltimeGoalsAgainst !== null) {
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
            $aggregation['matchIds'] = array_unique($aggregation['matchIds']);
            sort($aggregation['matchIds']);

            $aggregation['competitionIds'] = array_unique($aggregation['competitionIds']);
            sort($aggregation['competitionIds']);

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

    /**
     * @param FootballMatchTeamReferenceFrameAggregation[] $aggregations
     * @return FootballMatchTeamReferenceFrameAggregationAverage
     * @throws SerializerExceptionInterface
     */
    public function calculateMatchTeamReferenceFrameAggregationAverage(
        array $aggregations
    ): FootballMatchTeamReferenceFrameAggregationAverage
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

        $matchIds = array_unique($matchIds);
        sort($matchIds);

        $competitionIds = array_unique($competitionIds);
        sort($competitionIds);

        $teamIds = array_unique($teamIds);
        sort($teamIds);

        $numTeams = count($teamIds);
        $data = [
            'matchIds' => $matchIds,
            'competitionIds' => $competitionIds,
            'teamIds' => $teamIds
        ];
        foreach ($totals as $field => $total) {
            $data[$field] = (float)($numTeams === 0 ? 0.0 : ($total / $numTeams));
        }

        return $this->denormalizer->denormalize($data, FootballMatchTeamReferenceFrameAggregationAverage::class);
    }

    /**
     * @param FootballMatchTeamReferenceFrameAggregation[] $aggregations
     * @return array<string, FootballTeamStrength>
     */
    public function calculateTeamStrengths(
        array                                             $aggregations,
        FootballMatchTeamReferenceFrameAggregationAverage $aggregationAverage
    ): array
    {
        $numTeams = count($aggregations);
        if ($numTeams === 0) {
            return [];
        }

        $teamStrengths = [];
        foreach ($aggregations as $aggregation) {
            $teamStrengths[(string)$aggregation->teamId] = new FootballTeamStrength(
                matchIds: $aggregation->matchIds,
                competitionIds: $aggregation->competitionIds,
                teamId: $aggregation->teamId,
                attack: (float)(empty($aggregationAverage->goalsForPerFulltime)
                    ? 0.0
                    : ($aggregation->goalsForPerFulltime / $aggregationAverage->goalsForPerFulltime)),
                defense: (float)(empty($aggregationAverage->goalsAgainstPerFulltime)
                    ? 0.0
                    : ($aggregation->goalsAgainstPerFulltime / $aggregationAverage->goalsAgainstPerFulltime))
            );
        }
        return $teamStrengths;
    }

    /**
     * @param FootballMatch[] $matches
     * @param FootballCompetitionAverageGoalsForPerFulltime $competitionAverageGoalsForPerFulltime
     * @param array<string, FootballTeamStrength> $teamStrengths
     * @return array<string, FootballMatchXG>
     */
    public function calculateMatchXGsFromTeamStrengths(
        array                                         $matches,
        FootballCompetitionAverageGoalsForPerFulltime $competitionAverageGoalsForPerFulltime,
        array                                         $teamStrengths
    ): array
    {
        if (count($matches) === 0) {
            return [];
        }

        $defaultTeamStrength = new FootballTeamStrength([], [], 0, 0, 0);
        $matchXGs = [];
        foreach ($matches as $match) {
            $matchId = $match->getId();
            $homeTeamId = $match->getHomeTeam()->getId();
            $awayTeamId = $match->getAwayTeam()->getId();

            if ($matchId === null) {
                continue;
            }

            $homeTeamStrength = $teamStrengths[$homeTeamId] ?? $defaultTeamStrength;
            $awayTeamStrength = $teamStrengths[$awayTeamId] ?? $defaultTeamStrength;

            $matchXGs[(string)$matchId] = new FootballMatchXG(
                matchId: $matchId,
                homeTeam: $competitionAverageGoalsForPerFulltime->homeTeam * $homeTeamStrength->attack * $awayTeamStrength->defense,
                awayTeam: $competitionAverageGoalsForPerFulltime->awayTeam * $awayTeamStrength->attack * $homeTeamStrength->defense,
            );
        }

        return $matchXGs;
    }

    /**
     * @param FootballMatch[] $matches
     * @param FootballCompetitionTeamEntry[] $entries
     * @return array<string, FootballMatchXG>
     */
    public function calculateMatchXGsFromCompetitionTeamEntries(array $matches, array $entries): array
    {
        $topSeed = null;
        $bottomSeed = null;
        $teamSeeds = [];
        foreach ($entries as $entry) {
            $seed = $entry->getSeed();
            $teamSeeds[(string)$entry->getTeam()->getId()] = $seed;

            if ($seed !== null) {
                if ($topSeed === null || $seed < $topSeed) {
                    $topSeed = $seed;
                }
                if ($bottomSeed === null || $seed > $bottomSeed) {
                    $bottomSeed = $seed;
                }
            }
        }

        if ($topSeed === null || $bottomSeed === null) {
            return [];
        }

        $matchXGs = [];
        foreach ($matches as $match) {
            $matchId = $match->getId();

            $homeTeamSeed = $teamSeeds[$match->getHomeTeam()?->getId()] ?? null;
            $awayTeamSeed = $teamSeeds[$match->getAwayTeam()?->getId()] ?? null;

            if ($matchId === null || $homeTeamSeed === null || $awayTeamSeed === null) {
                continue;
            }

            $direction = $homeTeamSeed < $awayTeamSeed ? 1.0 : ($homeTeamSeed > $awayTeamSeed ? -1.0 : 0.0);
            $norm = (float)($bottomSeed <= $topSeed ? 1.0 : abs($homeTeamSeed - $awayTeamSeed) / ($bottomSeed - $topSeed));
            $epsilon = $direction * $norm;

            $matchXGs[(string)$matchId] = new FootballMatchXG(
                matchId: $matchId,
                homeTeam: 1 + $epsilon,
                awayTeam: 1 - $epsilon
            );
        }

        return $matchXGs;
    }

    /**
     * @param FootballMatchXG $a
     * @param FootballMatchXG $b
     * @param float $t
     * @return FootballMatchXG
     */
    public function calculateMatchGXLerp(FootballMatchXG $a, FootballMatchXG $b, float $t): FootballMatchXG
    {
        $t = max(0.0, min(1.0, $t));
        return new FootballMatchXG(
            matchId: $a->matchId,
            homeTeam: $a->homeTeam + $t * ($b->homeTeam - $a->homeTeam),
            awayTeam: $a->awayTeam + $t * ($b->awayTeam - $a->awayTeam),
        );
    }
}
