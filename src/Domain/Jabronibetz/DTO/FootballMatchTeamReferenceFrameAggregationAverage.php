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

namespace App\Domain\Jabronibetz\DTO;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final readonly class FootballMatchTeamReferenceFrameAggregationAverage
{
    /**
     * @param int[] $matchIds
     * @param int[] $competitionIds
     * @param array $teamIds
     * @param float $matches
     * @param float $halftimesPlayed
     * @param float $fulltimesPlayed
     * @param float $extraHalftimesPlayed
     * @param float $extraFulltimesPlayed
     * @param float $shootoutsPlayed
     * @param float $halftimeGoalsFor
     * @param float $fulltimeGoalsFor
     * @param float $extraHalftimeGoalsFor
     * @param float $extraFulltimeGoalsFor
     * @param float $shootoutGoalsFor
     * @param float $halftimeGoalsAgainst
     * @param float $fulltimeGoalsAgainst
     * @param float $extraHalftimeGoalsAgainst
     * @param float $extraFulltimeGoalsAgainst
     * @param float $shootoutGoalsAgainst
     * @param float $goalsForPerHalftime
     * @param float $goalsForPerFulltime
     * @param float $goalsForPerExtraHalftime
     * @param float $goalsForPerExtraFulltime
     * @param float $goalsForPerShootout
     * @param float $goalsAgainstPerHalftime
     * @param float $goalsAgainstPerFulltime
     * @param float $goalsAgainstPerExtraHalftime
     * @param float $goalsAgainstPerExtraFulltime
     * @param float $goalsAgainstPerShootout
     * @param float $halftimeGoalDifference
     * @param float $fulltimeGoalDifference
     * @param float $extraHalftimeGoalDifference
     * @param float $extraFulltimeGoalDifference
     * @param float $shootoutGoalDifference
     */
    public function __construct(
        public array $matchIds,
        public array $competitionIds,
        public array $teamIds,

        public float $matches,

        public float $halftimesPlayed,
        public float $fulltimesPlayed,
        public float $extraHalftimesPlayed,
        public float $extraFulltimesPlayed,
        public float $shootoutsPlayed,

        public float $halftimeGoalsFor,
        public float $fulltimeGoalsFor,
        public float $extraHalftimeGoalsFor,
        public float $extraFulltimeGoalsFor,
        public float $shootoutGoalsFor,

        public float $halftimeGoalsAgainst,
        public float $fulltimeGoalsAgainst,
        public float $extraHalftimeGoalsAgainst,
        public float $extraFulltimeGoalsAgainst,
        public float $shootoutGoalsAgainst,

        public float $goalsForPerHalftime,
        public float $goalsForPerFulltime,
        public float $goalsForPerExtraHalftime,
        public float $goalsForPerExtraFulltime,
        public float $goalsForPerShootout,

        public float $goalsAgainstPerHalftime,
        public float $goalsAgainstPerFulltime,
        public float $goalsAgainstPerExtraHalftime,
        public float $goalsAgainstPerExtraFulltime,
        public float $goalsAgainstPerShootout,

        public float $halftimeGoalDifference,
        public float $fulltimeGoalDifference,
        public float $extraHalftimeGoalDifference,
        public float $extraFulltimeGoalDifference,
        public float $shootoutGoalDifference
    )
    {
    }
}
