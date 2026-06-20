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
use App\Domain\Jabronibetz\Repository\FootballMatchRepository;
use App\Domain\Jabronibetz\Repository\FootballMatchTeamReferenceFrameRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
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
     * @return FootballMatchTeamReferenceFrame[]|array<string, FootballMatchTeamReferenceFrame[]>
     */
    public function getFulltimeMatchTeamReferenceFrames(FootballCompetition $competition, bool $group = false): array
    {
        /** @var FootballMatchTeamReferenceFrameRepository $matchTeamReferenceFrameRepo */
        $matchTeamReferenceFrameRepo = $this->entityManager->getRepository(FootballMatchTeamReferenceFrame::class);
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('competition', $competition))
            ->andWhere(Criteria::expr()->isNotNull('fulltimeGoalsFor'))
            ->andWhere(Criteria::expr()->isNotNull('fulltimeGoalsAgainst'))
            ->orderBy([
                'round' => Order::Ascending,
                'timestamp' => Order::Ascending
            ]);

        if ($group) {
            $groupRounds = $competition->getGroupRounds();
            if (empty($groupRounds) || $groupRounds < 0) {
                throw new ValueError(
                    sprintf('Invalid group rounds set for football competition id %d', $competition->getId() ?? 0)
                );
            }
            $matchTeamReferenceFramesByGroup = [];
            foreach ($competition->getTeamsByGroup() as $teamGroup => $teams) {
                $groupCriteria = (clone $criteria)
                    ->andWhere(Criteria::expr()->lte('round', $groupRounds))
                    ->andWhere(Criteria::expr()->in('team', $teams->toArray()));
                $matchTeamReferenceFramesByGroup[$teamGroup] = $matchTeamReferenceFrameRepo
                    ->matching($groupCriteria)
                    ->toArray();
            }
            return $matchTeamReferenceFramesByGroup;
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
        $matchTeamReferenceFrames = $this->getFulltimeMatchTeamReferenceFrames($competition, $group);
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

    /**
     * @param FootballCompetition $competition
     * @param bool $group
     * @param int|null $limit
     * @return FootballMatch[]|array<string, FootballMatch[]>
     */
    public function getNonFulltimeMatches(FootballCompetition $competition, bool $group = false, ?int $limit = null): array
    {
        /** @var FootballMatchRepository $matchRepo */
        $matchRepo = $this->entityManager->getRepository(FootballMatch::class);
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('competition', $competition))
            ->andWhere(Criteria::expr()->isNull('homeTeamFulltimeScore'))
            ->andWhere(Criteria::expr()->isNull('awayTeamFulltimeScore'))
            ->orderBy([
                'round' => Order::Ascending,
                'timestamp' => Order::Ascending
            ])
            ->setMaxResults($limit);

        if ($group) {
            $groupRounds = $competition->getGroupRounds();
            if (empty($groupRounds) || $groupRounds < 0) {
                throw new ValueError(
                    sprintf('Invalid group rounds set for football competition id %d', $competition->getId() ?? 0)
                );
            }
            $matchesByGroup = [];
            foreach ($competition->getTeamsByGroup() as $teamGroup => $teams) {
                $groupCriteria = (clone $criteria)
                    ->andWhere(Criteria::expr()->lte('round', $groupRounds))
                    ->andWhere(
                        Criteria::expr()->orX(
                            Criteria::expr()->in('homeTeam', $teams->toArray()),
                            Criteria::expr()->in('awayTeam', $teams->toArray())
                        )
                    );

                $matchesByGroup[$teamGroup] = $matchRepo
                    ->matching($groupCriteria)
                    ->toArray();
            }
            return $matchesByGroup;
        }

        return $matchRepo->matching($criteria)->toArray();
    }

    /**
     * @param FootballCompetition $competition
     * @param bool $group
     * @param int|null $limit
     * @return array<string, FootballMatchXG>|array<string, array<string, FootballMatchXG>>
     */
    public function getTeamEntryMatchXGs(FootballCompetition $competition, bool $group = false, ?int $limit = null): array
    {
        $matches = $this->getNonFulltimeMatches($competition, $group, $limit);
        $entries = $competition->getTeamEntries()->toArray();
        return $group
            ? array_map(
                fn ($groupMatches) => $this->footballCalculator->calculateMatchXGsFromCompetitionTeamEntries(
                    $groupMatches,
                    $entries
                ),
                $matches
            )
            : $this->footballCalculator->calculateMatchXGsFromCompetitionTeamEntries($matches, $entries);
    }

    /**
     * @param FootballCompetition $competition
     * @param bool $group
     * @param int|null $limit
     * @return array<string, FootballMatchXG>|array<string, array<string, FootballMatchXG>>
     * @throws SerializerExceptionInterface
     */
    public function getTeamStrengthMatchXGs(FootballCompetition $competition, bool $group = false, int $limit = null): array
    {
        $matches = $this->getNonFulltimeMatches($competition, $group, $limit);
        $teamStrengths = $this->getTeamStrengths($competition, $group);
        $matchTeamReferenceFrames = $this->getFulltimeMatchTeamReferenceFrames($competition, $group);

        if ($competition->getSeparateMatchXgHomeAway()) {
            /** @var FootballMatchTeamReferenceFrame[] $homeMatchTeamReferenceFrames */
            $homeMatchTeamReferenceFrames = [];
            /** @var FootballMatchTeamReferenceFrame[] $awayMatchTeamReferenceFrames */
            $awayMatchTeamReferenceFrames = [];

            if ($group) {
                $matchXGs = [];
                foreach ($matchTeamReferenceFrames as $matchGroup => $groupMatchTeamReferenceFrames) {
                    foreach ($groupMatchTeamReferenceFrames as $groupMatchTeamReferenceFrame) {
                        if ($groupMatchTeamReferenceFrame->isHomeTeam()) {
                            $homeMatchTeamReferenceFrames[] = $groupMatchTeamReferenceFrame;
                        }
                        if ($groupMatchTeamReferenceFrame->isAwayTeam()) {
                            $awayMatchTeamReferenceFrames[] = $groupMatchTeamReferenceFrame;
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

                    $groupCompetitionAverageGoalsForPerFulltime = new FootballCompetitionAverageGoalsForPerFulltime(
                        matchIds: $matchIds,
                        competitionIds: $competitionIds,
                        teamIds: $teamIds,
                        homeTeam: $homeMatchTeamReferenceFrameAggregationAverage->goalsForPerFulltime,
                        awayTeam: $awayMatchTeamReferenceFrameAggregationAverage->goalsForPerFulltime
                    );

                    $matchXGs[$matchGroup] = $this->footballCalculator->calculateMatchXGsFromTeamStrengths(
                        $matches[$matchGroup],
                        $groupCompetitionAverageGoalsForPerFulltime,
                        $teamStrengths
                    );
                }
                return $matchXGs;
            }

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

            return $this->footballCalculator->calculateMatchXGsFromTeamStrengths(
                $matches,
                $competitionAverageGoalsForPerFulltime,
                $teamStrengths
            );
        }

        if ($group) {
            $matchXGs = [];
            foreach ($matchTeamReferenceFrames as $matchGroup => $groupMatchTeamReferenceFrames) {
                $groupMatchTeamReferenceFrameAggregationAverage = $this->footballCalculator
                    ->calculateMatchTeamReferenceFrameAggregationAverage(
                        $this->footballCalculator->calculateMatchTeamReferenceFrameAggregations(
                            $groupMatchTeamReferenceFrames
                        )
                    );

                $groupCompetitionAverageGoalsForPerFulltime = new FootballCompetitionAverageGoalsForPerFulltime(
                    matchIds: $groupMatchTeamReferenceFrameAggregationAverage->matchIds,
                    competitionIds: $groupMatchTeamReferenceFrameAggregationAverage->competitionIds,
                    teamIds: $groupMatchTeamReferenceFrameAggregationAverage->teamIds,
                    homeTeam: $groupMatchTeamReferenceFrameAggregationAverage->goalsForPerFulltime,
                    awayTeam: $groupMatchTeamReferenceFrameAggregationAverage->goalsForPerFulltime
                );

                $matchXGs[$matchGroup] = $this->footballCalculator->calculateMatchXGsFromTeamStrengths(
                    $matches[$matchGroup],
                    $groupCompetitionAverageGoalsForPerFulltime,
                    $teamStrengths[$matchGroup]
                );
            }
            return $matchXGs;
        }

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

        return $this->footballCalculator->calculateMatchXGsFromTeamStrengths(
            $matches,
            $competitionAverageGoalsForPerFulltime,
            $teamStrengths
        );
    }
}
