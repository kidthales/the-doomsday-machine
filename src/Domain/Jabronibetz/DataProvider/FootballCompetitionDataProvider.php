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

use App\Domain\Jabronibetz\Calculator\FootballCalculator;
use App\Domain\Jabronibetz\Calculator\FootballCalculatorAwareTrait;
use App\Domain\Jabronibetz\DTO\FootballCompetitionAverageGoalsForPerFulltime;
use App\Domain\Jabronibetz\DTO\FootballMatchScoreProbabilityDistribution;
use App\Domain\Jabronibetz\DTO\FootballMatchTeamReferenceFrameAggregation;
use App\Domain\Jabronibetz\DTO\FootballMatchTeamReferenceFrameAggregationAverage;
use App\Domain\Jabronibetz\DTO\FootballMatchXG;
use App\Domain\Jabronibetz\DTO\FootballMatchXGLerp;
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
    use EntityManagerAwareTrait;

    /**
     * @param FootballCalculator $footballCalculator
     */
    public function __construct(private readonly FootballCalculator $footballCalculator)
    {
    }

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
    ): FootballMatchTeamReferenceFrameAggregationAverage|array
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
        $defaultTeamStrength = fn(mixed $teamId) => new FootballTeamStrength(
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
    public function getNonFulltimeMatches(
        FootballCompetition $competition,
        bool                $group = false,
        ?int                $limit = null
    ): array
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
    public function getTeamEntryMatchXGs(
        FootballCompetition $competition,
        bool                $group = false,
        ?int                $limit = null
    ): array
    {
        $matches = $this->getNonFulltimeMatches($competition, $group, $limit);
        $entries = $competition->getTeamEntries()->toArray();
        return $group
            ? array_map(
                fn($groupMatches) => $this->footballCalculator->calculateMatchXGsFromCompetitionTeamEntries(
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
     * @return FootballCompetitionAverageGoalsForPerFulltime|array<string, FootballCompetitionAverageGoalsForPerFulltime>
     * @throws SerializerExceptionInterface
     */
    public function getAverageGoalsForPerFulltime(
        FootballCompetition $competition,
        bool                $group = false
    ): FootballCompetitionAverageGoalsForPerFulltime|array
    {
        $matchTeamReferenceFrames = $this->getFulltimeMatchTeamReferenceFrames($competition, $group);
        $calculateMatchTeamReferenceFrameAggregationAverage
            = fn(array $matchTeamReferenceFrames) => $this->footballCalculator
            ->calculateMatchTeamReferenceFrameAggregationAverage(
                $this->footballCalculator->calculateMatchTeamReferenceFrameAggregations(
                    $matchTeamReferenceFrames
                )
            );
        $createCompetitionAverageGoalsForPerFulltime = function (
            FootballMatchTeamReferenceFrameAggregationAverage $homeMatchTeamReferenceFrameAggregationAverage,
            FootballMatchTeamReferenceFrameAggregationAverage $awayMatchTeamReferenceFrameAggregationAverage
        ) {
            /** @var int[] $matchIds */
            $matchIds = array_unique([
                ...$homeMatchTeamReferenceFrameAggregationAverage->matchIds,
                ...$awayMatchTeamReferenceFrameAggregationAverage->matchIds
            ]);
            sort($matchIds);
            /** @var int[] $competitionIds */
            $competitionIds = array_unique([
                ...$homeMatchTeamReferenceFrameAggregationAverage->competitionIds,
                ...$awayMatchTeamReferenceFrameAggregationAverage->competitionIds
            ]);
            sort($competitionIds);
            /** @var int[] $teamIds */
            $teamIds = array_unique([
                ...$homeMatchTeamReferenceFrameAggregationAverage->teamIds,
                ...$awayMatchTeamReferenceFrameAggregationAverage->teamIds
            ]);
            sort($teamIds);
            return new FootballCompetitionAverageGoalsForPerFulltime(
                matchIds: $matchIds,
                competitionIds: $competitionIds,
                teamIds: $teamIds,
                homeTeam: $homeMatchTeamReferenceFrameAggregationAverage->goalsForPerFulltime,
                awayTeam: $awayMatchTeamReferenceFrameAggregationAverage->goalsForPerFulltime
            );
        };

        if ($competition->getSeparateMatchXgHomeAway()) {
            /** @var FootballMatchTeamReferenceFrame[] $homeMatchTeamReferenceFrames */
            $homeMatchTeamReferenceFrames = [];
            /** @var FootballMatchTeamReferenceFrame[] $awayMatchTeamReferenceFrames */
            $awayMatchTeamReferenceFrames = [];
            $processMatchTeamReferenceFrames = function (array $matchTeamReferenceFrames) use (&$homeMatchTeamReferenceFrames, &$awayMatchTeamReferenceFrames) {
                foreach ($matchTeamReferenceFrames as $matchTeamReferenceFrame) {
                    if ($matchTeamReferenceFrame->isHomeTeam()) {
                        $homeMatchTeamReferenceFrames[] = $matchTeamReferenceFrame;
                    }
                    if ($matchTeamReferenceFrame->isAwayTeam()) {
                        $awayMatchTeamReferenceFrames[] = $matchTeamReferenceFrame;
                    }
                }
            };

            if ($group) {
                $competitionAverageGoalsForPerFulltimeByGroup = [];
                foreach ($matchTeamReferenceFrames as $matchGroup => $groupMatchTeamReferenceFrames) {
                    $processMatchTeamReferenceFrames($groupMatchTeamReferenceFrames);
                    $homeMatchTeamReferenceFrameAggregationAverage = $calculateMatchTeamReferenceFrameAggregationAverage(
                        $homeMatchTeamReferenceFrames
                    );
                    $awayMatchTeamReferenceFrameAggregationAverage = $calculateMatchTeamReferenceFrameAggregationAverage(
                        $awayMatchTeamReferenceFrames
                    );
                    $competitionAverageGoalsForPerFulltimeByGroup[$matchGroup] = $createCompetitionAverageGoalsForPerFulltime(
                        $homeMatchTeamReferenceFrameAggregationAverage,
                        $awayMatchTeamReferenceFrameAggregationAverage
                    );
                }
                return $competitionAverageGoalsForPerFulltimeByGroup;
            }

            $processMatchTeamReferenceFrames($matchTeamReferenceFrames);
            $homeMatchTeamReferenceFrameAggregationAverage = $calculateMatchTeamReferenceFrameAggregationAverage(
                $homeMatchTeamReferenceFrames
            );
            $awayMatchTeamReferenceFrameAggregationAverage = $calculateMatchTeamReferenceFrameAggregationAverage(
                $awayMatchTeamReferenceFrames
            );
            return $createCompetitionAverageGoalsForPerFulltime(
                $homeMatchTeamReferenceFrameAggregationAverage,
                $awayMatchTeamReferenceFrameAggregationAverage
            );
        }

        if ($group) {
            $competitionAverageGoalsForPerFulltimeByGroup = [];
            foreach ($matchTeamReferenceFrames as $matchGroup => $groupMatchTeamReferenceFrames) {
                $matchTeamReferenceFrameAggregationAverage = $calculateMatchTeamReferenceFrameAggregationAverage(
                    $groupMatchTeamReferenceFrames
                );
                $competitionAverageGoalsForPerFulltimeByGroup[$matchGroup] = $createCompetitionAverageGoalsForPerFulltime(
                    $matchTeamReferenceFrameAggregationAverage,
                    $matchTeamReferenceFrameAggregationAverage
                );
            }
            return $competitionAverageGoalsForPerFulltimeByGroup;
        }

        $matchTeamReferenceFrameAggregationAverage = $calculateMatchTeamReferenceFrameAggregationAverage(
            $matchTeamReferenceFrames
        );
        return $createCompetitionAverageGoalsForPerFulltime(
            $matchTeamReferenceFrameAggregationAverage,
            $matchTeamReferenceFrameAggregationAverage
        );
    }

    /**
     * @param FootballCompetition $competition
     * @param bool $group
     * @param int|null $limit
     * @return array<string, FootballMatchXG>|array<string, array<string, FootballMatchXG>>
     * @throws SerializerExceptionInterface
     */
    public function getTeamStrengthMatchXGs(
        FootballCompetition $competition,
        bool                $group = false,
        ?int                $limit = null
    ): array
    {
        $matches = $this->getNonFulltimeMatches($competition, $group, $limit);
        $teamStrengths = $this->getTeamStrengths($competition, $group);
        $competitionAverageGoalsForPerFulltime = $this->getAverageGoalsForPerFulltime($competition, $group);

        if ($group) {
            $matchXGsByGroup = [];
            foreach ($matches as $matchGroup => $groupMatches) {
                $matchXGsByGroup[$matchGroup] = $this->footballCalculator->calculateMatchXGsFromTeamStrengths(
                    $groupMatches,
                    $competitionAverageGoalsForPerFulltime[$matchGroup],
                    $teamStrengths[$matchGroup]
                );
            }
            return $matchXGsByGroup;
        }

        return $this->footballCalculator->calculateMatchXGsFromTeamStrengths(
            $matches,
            $competitionAverageGoalsForPerFulltime,
            $teamStrengths
        );
    }

    /**
     * @param FootballCompetition $competition
     * @param bool $group
     * @param int|null $limit
     * @return array<string, FootballMatchXGLerp>|array<string, array<string, FootballMatchXGLerp>>
     * @throws SerializerExceptionInterface
     */
    public function getMatchXGLerps(FootballCompetition $competition, bool $group = false, ?int $limit = null): array
    {
        $matches = $this->getNonFulltimeMatches($competition, $group, $limit);
        $teamEntryMatchXGs = $this->getTeamEntryMatchXGs($competition, $group, $limit);
        $teamStrengthMatchXGs = $this->getTeamStrengthMatchXGs($competition, $group, $limit);

        if ($group) {
            $matchXGLerpsByGroup = [];
            foreach ($matches as $matchGroup => $groupMatches) {
                if (!isset($matchXGLerpsByGroup[$matchGroup])) {
                    $matchXGLerpsByGroup[$matchGroup] = [];
                }
                foreach ($groupMatches as $groupMatch) {
                    $matchId = (string)$groupMatch->getId();
                    $matchXGLerpsByGroup[$matchGroup][$matchId] = $this->footballCalculator->calculateMatchGXLerp(
                        $teamEntryMatchXGs[$matchGroup][$matchId],
                        $teamStrengthMatchXGs[$matchGroup][$matchId],
                        ($groupMatch->getRound() - 1) / $competition->getGroupRounds()
                    );
                }
            }
            return $matchXGLerpsByGroup;
        }

        $matchXGLerps = [];
        foreach ($matches as $match) {
            $matchId = (string)$match->getId();
            $matchXGLerps[$matchId] = $this->footballCalculator->calculateMatchGXLerp(
                $teamEntryMatchXGs[$matchId],
                $teamStrengthMatchXGs[$matchId],
                ($match->getRound() - 1) / $competition->getRounds()
            );
        }
        return $matchXGLerps;
    }

    /**
     * @param FootballCompetition $competition
     * @param bool $group
     * @param int|null $limit
     * @return array<string, FootballMatchScoreProbabilityDistribution>|array<string, array<string, FootballMatchScoreProbabilityDistribution>>
     * @throws SerializerExceptionInterface
     */
    public function getMatchScoreProbabilityDistributions(
        FootballCompetition $competition,
        bool                $group = false,
        ?int                $limit = null
    ): array
    {
        $maxGoals = 10;
        $matchXGs = $this->getMatchXGLerps($competition, $group, $limit);
        return $group
            ? array_map(
                fn($groupMatchXGs) => $this->footballCalculator->calculateMatchScoreProbabilityDistributions(
                    $groupMatchXGs,
                    $maxGoals
                ),
                $matchXGs
            )
            : $this->footballCalculator->calculateMatchScoreProbabilityDistributions($matchXGs, $maxGoals);
    }
}
