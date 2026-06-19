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

namespace App\Domain\Jabronibetz\DataProvider;

use App\Domain\Jabronibetz\Calculator\FootballCalculatorAwareTrait;
use App\Domain\Jabronibetz\DTO\FootballCompetitionAverageGoalsForPerFulltime;
use App\Domain\Jabronibetz\DTO\FootballMatchTeamReferenceFrameAggregation;
use App\Domain\Jabronibetz\DTO\FootballMatchTeamReferenceFrameAggregationAverage;
use App\Domain\Jabronibetz\DTO\FootballMatchXG;
use App\Domain\Jabronibetz\DTO\FootballTeamStrength;
use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballMatch;
use App\Domain\Jabronibetz\Entity\FootballMatchTeamReferenceFrame;
use App\Domain\Jabronibetz\ORM\EntityManagerAwareTrait;
use App\Domain\Jabronibetz\Repository\FootballMatchTeamReferenceFrameRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use ValueError;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class FootballCompetitionDataProvider
{
    use EntityManagerAwareTrait, FootballCalculatorAwareTrait;

    /**
     * @param FootballCompetition $competition
     * @param bool $group
     * @return FootballMatchTeamReferenceFrame[]
     */
    public function getMatchTeamReferenceFrames(FootballCompetition $competition, bool $group = false): array
    {
        /** @var FootballMatchTeamReferenceFrameRepository $matchTeamReferenceFrameRepo */
        $matchTeamReferenceFrameRepo = $this->entityManager->getRepository(FootballMatchTeamReferenceFrame::class);
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('competition', $competition))
            ->andWhere(Criteria::expr()->isNotNull('fulltimeGoalsFor'))
            ->andWhere(Criteria::expr()->isNotNull('fulltimeGoalsAgainst'));

        if ($group) {
            $groupRounds = $competition->getGroupRounds();
            if (empty($groupRounds) || $groupRounds < 0) {
                throw new ValueError(
                    sprintf('Invalid group rounds set for football competition id %d', $competition->getId() ?? 0)
                );
            }
            $matchTeamReferenceFrameByGroup = [];
            foreach ($competition->getTeamsByGroup() as $teamGroup => $teams) {
                $groupCriteria = (clone $criteria)
                    ->andWhere(Criteria::expr()->lte('round', $groupRounds))
                    ->andWhere(Criteria::expr()->in('team', $teams->toArray()));
                $matchTeamReferenceFrameByGroup[$teamGroup] = $matchTeamReferenceFrameRepo
                    ->matching($groupCriteria)
                    ->toArray();
            }
            return $matchTeamReferenceFrameByGroup;
        }

        return $matchTeamReferenceFrameRepo->matching($criteria)->toArray();
    }

    /**
     * @param FootballCompetition $competition
     * @param bool $group
     * @return FootballMatchTeamReferenceFrameAggregation[]|array<string, FootballMatchTeamReferenceFrameAggregation[]>
     * @throws SerializerExceptionInterface
     */
    public function getMatchTeamReferenceFrameAggregations(
        FootballCompetition $competition,
        bool                $group = false
    ): array
    {
        $matchTeamReferenceFrames = $this->getMatchTeamReferenceFrames($competition, $group);
        return $group
            ? array_map(
                fn($referenceFrames) => $this->footballCalculator->calculateMatchTeamReferenceFrameAggregations(
                    $referenceFrames
                ),
                $matchTeamReferenceFrames
            )
            : $this->footballCalculator->calculateMatchTeamReferenceFrameAggregations($matchTeamReferenceFrames);
    }

    /**
     * @param FootballCompetition $competition
     * @param bool $group
     * @return FootballMatchTeamReferenceFrameAggregationAverage|array<string, FootballMatchTeamReferenceFrameAggregationAverage>
     * @throws SerializerExceptionInterface
     */
    public function getMatchTeamReferenceFrameAggregationAverage(
        FootballCompetition $competition,
        bool                $group = false
    ): FootballMatchTeamReferenceFrameAggregationAverage | array
    {
        $matchTeamReferenceFrameAggregations = $this->getMatchTeamReferenceFrameAggregations($competition, $group);
        return $group
            ? array_map(
                fn($aggregations) => $this->footballCalculator->calculateMatchTeamReferenceFrameAggregationAverage(
                    $aggregations
                ),
                $matchTeamReferenceFrameAggregations
            )
            : $this->footballCalculator->calculateMatchTeamReferenceFrameAggregationAverage(
                $matchTeamReferenceFrameAggregations
            );
    }

    /**
     * @param FootballCompetition $competition
     * @param bool $group
     * @return array<string, FootballTeamStrength>|array<string, array<string, FootballTeamStrength>>
     * @throws SerializerExceptionInterface
     */
    public function getTeamStrengths(FootballCompetition $competition, bool $group = false): array
    {
        $matchTeamReferenceFrameAggregations = $this->getMatchTeamReferenceFrameAggregations($competition, $group);
        $defaultTeamStrength = fn (mixed $teamId) => new FootballTeamStrength(
            matchIds: [],
            competitionIds: [$competition->getId()],
            teamId: (int)$teamId,
            attack: 0,
            defense: 0
        );

        if ($group) {
            $teamStrengths = array_map(
                fn($aggregations) => $this->footballCalculator->calculateTeamStrengths(
                    $aggregations,
                    $this->footballCalculator->calculateMatchTeamReferenceFrameAggregationAverage($aggregations)
                ),
                $matchTeamReferenceFrameAggregations
            );
            foreach ($competition->getTeamsByGroup() as $teamGroup => $teams) {
                foreach ($teams as $team) {
                    $teamId = (string)$team->getId();
                    if (!isset($teamStrengths[$teamGroup])) {
                        $teamStrengths[$teamGroup] = [];
                    }
                    if (!isset($teamStrengths[$teamGroup][$teamId])) {
                        $teamStrengths[$teamGroup][$teamId] = $defaultTeamStrength($teamId);
                    }
                }
            }
        } else {
            $teamStrengths = $this->footballCalculator->calculateTeamStrengths(
                $matchTeamReferenceFrameAggregations,
                $this->footballCalculator->calculateMatchTeamReferenceFrameAggregationAverage(
                    $matchTeamReferenceFrameAggregations
                )
            );
            foreach ($competition->getTeams() as $team) {
                $teamId = (string)$team->getId();
                if (!isset($teamStrengths[$teamId])) {
                    $teamStrengths[$teamId] = $defaultTeamStrength($teamId);
                }
            }
        }
        return $teamStrengths;
    }

    public function getTeamEntryMatchXGs(FootballCompetition $competition, bool $group = false): array
    {
        // TODO
        return $this->footballCalculator->calculateMatchXGsFromCompetitionTeamEntries(
            $competition->getMatches()
                ->filter(fn(FootballMatch $match) => $match->getHomeTeamFulltimeScore() === null && $match->getAwayTeamFulltimeScore() === null)
                ->toArray(),
            $competition->getTeamEntries()->toArray()
        );
    }

    /**
     * @param FootballCompetition $competition
     * @param bool $group
     * @return FootballMatchXG[]
     * @throws SerializerExceptionInterface
     */
    public function getTeamStrengthMatchXGs(FootballCompetition $competition, bool $group = false): array
    {
        $matches = $competition->getMatches()
            ->filter(fn(FootballMatch $match) => $match->getHomeTeamFulltimeScore() === null && $match->getAwayTeamFulltimeScore() === null)
            ->toArray();
        $teamEntryMatchXGs = $this->footballCalculator->calculateMatchXGsFromCompetitionTeamEntries(
            $matches,
            $competition->getTeamEntries()->toArray()
        );
        $teamStrengths = $this->getTeamStrengths($competition, $group);
        $matchTeamReferenceFrames = $this->getMatchTeamReferenceFrames($competition, $group);

        if ($competition->getSeparateMatchXgHomeAway()) {
            /** @var FootballMatchTeamReferenceFrame[] $homeMatchTeamReferenceFrames */
            $homeMatchTeamReferenceFrames = [];
            /** @var FootballMatchTeamReferenceFrame[] $awayMatchTeamReferenceFrames */
            $awayMatchTeamReferenceFrames = [];

            if ($group) {
                // TODO
                $teamStrengthMatchXGs = [];
            } else {
                foreach ($matchTeamReferenceFrames as $matchTeamReferenceFrame) {
                    if ($matchTeamReferenceFrame->isHomeTeam()) {
                        $homeMatchTeamReferenceFrames[] = $matchTeamReferenceFrame;
                    }
                    if ($matchTeamReferenceFrame->isAwayTeam()) {
                        $awayMatchTeamReferenceFrames[] = $matchTeamReferenceFrame;
                    }
                }

                $homeMatchTeamReferenceFrameAggregationAverage = $this->footballCalculator
                    ->calculateMatchTeamReferenceFrameAggregationAverage(
                        $this->footballCalculator->calculateMatchTeamReferenceFrameAggregations(
                            $homeMatchTeamReferenceFrames
                        )
                    );
                $awayMatchTeamReferenceFrameAggregationAverage = $this->footballCalculator
                    ->calculateMatchTeamReferenceFrameAggregationAverage(
                        $this->footballCalculator->calculateMatchTeamReferenceFrameAggregations(
                            $awayMatchTeamReferenceFrames
                        )
                    );

                $matchIds = array_unique([
                    ...$homeMatchTeamReferenceFrameAggregationAverage->matchIds,
                    ...$awayMatchTeamReferenceFrameAggregationAverage->matchIds
                ]);
                sort($matchIds);
                $competitionIds = array_unique([
                    ...$homeMatchTeamReferenceFrameAggregationAverage->competitionIds,
                    ...$awayMatchTeamReferenceFrameAggregationAverage->competitionIds
                ]);
                sort($competitionIds);
                $teamIds = array_unique([
                    ...$homeMatchTeamReferenceFrameAggregationAverage->teamIds,
                    ...$awayMatchTeamReferenceFrameAggregationAverage->teamIds
                ]);
                sort($teamIds);

                $competitionAverageGoalsForPerFulltime = new FootballCompetitionAverageGoalsForPerFulltime(
                    matchIds: $matchIds,
                    competitionIds: $competitionIds,
                    teamIds: $teamIds,
                    homeTeam: $homeMatchTeamReferenceFrameAggregationAverage->goalsForPerFulltime,
                    awayTeam: $awayMatchTeamReferenceFrameAggregationAverage->goalsForPerFulltime
                );

                $teamStrengthMatchXGs = $this->footballCalculator->calculateMatchXGsFromTeamStrengths(
                    $matches,
                    $competitionAverageGoalsForPerFulltime,
                    $teamStrengths
                );
            }
        } else {
            if ($group) {
                // TODO
                $teamStrengthMatchXGs = [];
            } else {
                $matchTeamReferenceFrameAggregationAverage = $this->footballCalculator
                    ->calculateMatchTeamReferenceFrameAggregationAverage(
                        $this->footballCalculator->calculateMatchTeamReferenceFrameAggregations(
                            $matchTeamReferenceFrames
                        )
                    );

                $competitionAverageGoalsForPerFulltime = new FootballCompetitionAverageGoalsForPerFulltime(
                    matchIds: $matchTeamReferenceFrameAggregationAverage->matchIds,
                    competitionIds: $matchTeamReferenceFrameAggregationAverage->competitionIds,
                    teamIds: $matchTeamReferenceFrameAggregationAverage->teamIds,
                    homeTeam: $matchTeamReferenceFrameAggregationAverage->goalsForPerFulltime,
                    awayTeam: $matchTeamReferenceFrameAggregationAverage->goalsForPerFulltime
                );

                $teamStrengthMatchXGs = $this->footballCalculator->calculateMatchXGsFromTeamStrengths(
                    $matches,
                    $competitionAverageGoalsForPerFulltime,
                    $teamStrengths
                );
            }
        }

        // TODO
        return [];
    }
}
