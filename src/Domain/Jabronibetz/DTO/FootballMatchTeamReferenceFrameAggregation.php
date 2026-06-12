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
readonly class FootballMatchTeamReferenceFrameAggregation
{
    /**
     * @param int[] $matchIds
     * @param int[] $competitionIds
     * @param int $teamId
     * @param int $matches
     * @param int $halftimesPlayed
     * @param int $fulltimesPlayed
     * @param int $extraHalftimesPlayed
     * @param int $extraFulltimesPlayed
     * @param int $shootoutsPlayed
     * @param int $halftimeGoalsFor
     * @param int $fulltimeGoalsFor
     * @param int $extraHalftimeGoalsFor
     * @param int $extraFulltimeGoalsFor
     * @param int $shootoutGoalsFor
     * @param int $halftimeGoalsAgainst
     * @param int $fulltimeGoalsAgainst
     * @param int $extraHalftimeGoalsAgainst
     * @param int $extraFulltimeGoalsAgainst
     * @param int $shootoutGoalsAgainst
     * @param string[] $sequence
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
     * @param int $halftimeGoalDifference
     * @param int $fulltimeGoalDifference
     * @param int $extraHalftimeGoalDifference
     * @param int $extraFulltimeGoalDifference
     * @param int $shootoutGoalDifference
     */
    public function __construct(
        public array $matchIds,
        public array $competitionIds,

        public int   $teamId,

        public int   $matches,

        public int   $halftimesPlayed,
        public int   $fulltimesPlayed,
        public int   $extraHalftimesPlayed,
        public int   $extraFulltimesPlayed,
        public int   $shootoutsPlayed,

        public int   $halftimeGoalsFor,
        public int   $fulltimeGoalsFor,
        public int   $extraHalftimeGoalsFor,
        public int   $extraFulltimeGoalsFor,
        public int   $shootoutGoalsFor,

        public int   $halftimeGoalsAgainst,
        public int   $fulltimeGoalsAgainst,
        public int   $extraHalftimeGoalsAgainst,
        public int   $extraFulltimeGoalsAgainst,
        public int   $shootoutGoalsAgainst,

        public array $sequence,

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

        public int   $halftimeGoalDifference,
        public int   $fulltimeGoalDifference,
        public int   $extraHalftimeGoalDifference,
        public int   $extraFulltimeGoalDifference,
        public int   $shootoutGoalDifference
    )
    {
    }
}
