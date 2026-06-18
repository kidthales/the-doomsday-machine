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

namespace App\Command\Jabronibetz;

use App\Domain\Jabronibetz\Calculator\FootballCalculatorAwareTrait;
use App\Domain\Jabronibetz\DTO\FootballCompetitionAverageGoalsForPerFulltime;
use App\Domain\Jabronibetz\DTO\FootballMatchTeamReferenceFrameAggregation;
use App\Domain\Jabronibetz\DTO\FootballMatchXG;
use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballMatch;
use App\Domain\Jabronibetz\Entity\FootballMatchTeamReferenceFrame;
use App\Domain\Jabronibetz\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command;
use App\Domain\Shared\Console\Style\DefinitionListConverterAwareTrait;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:jabronibetz:football-match-xg:list',
    description: 'List football match xg for given competition'
)]
final class FootballMatchXGListCommand extends Command
{
    use DefinitionListConverterAwareTrait,
        EntityManagerAwareTrait,
        FootballCalculatorAwareTrait;

    private const array HEADERS = ['Match', 'Home XG (Seed)', 'Away XG (Seed)', 'Home XG (Strength)', 'Away XG (Strength)', 'Home XG (Lerp)', 'Away XG (Lerp)'];

    /**
     * @param FootballCompetition $cmp
     * @param array $teams
     * @param int $groupRounds
     * @param bool|null $homeTeam
     * @param bool|null $awayTeam
     * @return Criteria
     */
    private static function createMatchTeamReferenceFrameCriteria(
        FootballCompetition $cmp,
        array               $teams = [],
        int                 $groupRounds = 0,
        ?bool               $homeTeam = null,
        ?bool               $awayTeam = null
    ): Criteria
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('competition', $cmp))
            ->andWhere(Criteria::expr()->isNotNull('fulltimeGoalsFor'))
            ->andWhere(Criteria::expr()->isNotNull('fulltimeGoalsAgainst'));

        if (!empty($teams)) {
            $criteria->andWhere(Criteria::expr()->in('team', $teams));
        }

        if ($groupRounds > 0) {
            $criteria->andWhere(Criteria::expr()->lte('round', $groupRounds));
        }

        if ($homeTeam !== null) {
            $criteria->andWhere(Criteria::expr()->eq('homeTeam', $homeTeam));
        }

        if ($awayTeam !== null) {
            $criteria->andWhere(Criteria::expr()->eq('awayTeam', $awayTeam));
        }

        return $criteria;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'competition-id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football competition'
            )
            ->addOption(
                name: 'group',
                mode: InputOption::VALUE_NONE,
                description: 'List & calculate football match xg by competition group'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to list the <comment>football team strength</comment>s
                for a <comment>football competition</comment> that exists in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <competition-id> [--group]</info>

                Examples:
                  <info>%command.full_name% 1</info>

                If no competition-id is specified, you'll be prompted interactively.
                HELP
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->interactChoiceQuestionWithChoosables(
            $input,
            $output,
            'competition-id',
            'Football competition id: ',
            $this->entityManager->getRepository(FootballCompetition::class)->findAll(),
            true
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Jabronibetz: List Football Match XGs');

        try {
            $cmp = $this->entityManager->find(FootballCompetition::class, $input->getArgument('competition-id'));
            if ($cmp === null) {
                $io->error('Football competition not found');
                return Command::FAILURE;
            }

            $io->section($cmp->getName());

            $matches = $cmp->getMatches()
                ->filter(fn(FootballMatch $match) => $match->getHomeTeamFulltimeScore() === null && $match->getAwayTeamFulltimeScore() === null)
                ->toArray();
            $teamSeedMatchXGs = $this->footballCalculator->calculateMatchXGsFromCompetitionTeamEntries($matches, $cmp->getTeamEntries()->toArray());

            if ($input->getOption('group')) {
                $groupRounds = $cmp->getGroupRounds();
                if ($groupRounds === null) {
                    $io->error('Football competition does not have a group phase');
                    return Command::FAILURE;
                }

                foreach ($cmp->getTeamsByGroup() as $group => $teams) {
                    /** @var FootballMatch[] $groupMatches */
                    $groupMatches = array_filter(
                        $matches,
                        fn($match) => $teams->findFirst(fn($ix, $team) => $match->getHomeTeam()->getId() === $team->getId() || $match->getAwayTeam()->getId() === $team->getId())
                    );
                    $aggregations = $this->calculateMatchTeamReferenceFrameAggregations(
                        self::createMatchTeamReferenceFrameCriteria($cmp, $teams->toArray(), $groupRounds)
                    );
                    $teamStrengthMatchXGs = $this->calculateMatchXGsFromTeamStrengths(
                        $groupMatches,
                        $this->calculateCompetitionAverageGoalsForPerFulltime($cmp, $aggregations),
                        $aggregations
                    );
                    $rows = [];
                    foreach ($groupMatches as $match) {
                        $matchId = (string)$match->getId();
                        $teamSeedMatchXG = $teamSeedMatchXGs[$matchId];
                        $teamStrengthMatchXG = $teamStrengthMatchXGs[$matchId];
                        $lerpMatchXG = $this->footballCalculator->calculateMatchGXLerp(
                            $teamSeedMatchXG,
                            $teamStrengthMatchXG,
                            ($match->getRound() - 1) / $cmp->getRounds()
                        );
                        $rows[] = [
                            $match->getChoiceValue(),
                            $teamSeedMatchXG->homeTeam,
                            $teamSeedMatchXG->awayTeam,
                            $teamStrengthMatchXG->homeTeam,
                            $teamStrengthMatchXG->awayTeam,
                            $lerpMatchXG->homeTeam,
                            $lerpMatchXG->awayTeam
                        ];
                    }
                    $table = new Table($output);
                    $table->setHeaderTitle(sprintf('Group %s', $group));
                    $table->setHeaders(self::HEADERS);
                    $table->setRows($rows);
                    $table->render();
                }
            } else {
                $aggregations = $this->calculateMatchTeamReferenceFrameAggregations(
                    self::createMatchTeamReferenceFrameCriteria($cmp)
                );
                $teamStrengthMatchXGs = $this->calculateMatchXGsFromTeamStrengths(
                    $matches,
                    $this->calculateCompetitionAverageGoalsForPerFulltime($cmp, $aggregations),
                    $aggregations
                );
                $rows = [];
                foreach ($matches as $match) {
                    $matchId = (string)$match->getId();
                    $teamSeedMatchXG = $teamSeedMatchXGs[$matchId];
                    $teamStrengthMatchXG = $teamStrengthMatchXGs[$matchId];
                    $lerpMatchXG = $this->footballCalculator->calculateMatchGXLerp(
                        $teamSeedMatchXG,
                        $teamStrengthMatchXG,
                        ($match->getRound() - 1) / $cmp->getRounds()
                    );
                    $rows[] = [
                        $match->getChoiceValue(),
                        $teamSeedMatchXG->homeTeam,
                        $teamSeedMatchXG->awayTeam,
                        $teamStrengthMatchXG->homeTeam,
                        $teamStrengthMatchXG->awayTeam,
                        $lerpMatchXG->homeTeam,
                        $lerpMatchXG->awayTeam
                    ];
                }
                $io->table(self::HEADERS, $rows);
            }
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param Criteria $criteria
     * @return FootballMatchTeamReferenceFrameAggregation[]
     * @throws SerializerExceptionInterface
     */
    private function calculateMatchTeamReferenceFrameAggregations(Criteria $criteria): array
    {
        return $this->footballCalculator->calculateMatchTeamReferenceFrameAggregations(
            $this->entityManager
                ->getRepository(FootballMatchTeamReferenceFrame::class)
                ->matching($criteria)
                ->toArray()
        );
    }

    /**
     * @param FootballCompetition $cmp
     * @param FootballMatchTeamReferenceFrameAggregation[] $aggregations
     * @return FootballCompetitionAverageGoalsForPerFulltime
     * @throws SerializerExceptionInterface
     */
    private function calculateCompetitionAverageGoalsForPerFulltime(
        FootballCompetition $cmp,
        array               $aggregations
    ): FootballCompetitionAverageGoalsForPerFulltime
    {
        if ($cmp->getSeparateMatchXgHomeAway()) {
            $homeTeamAggregationAverage = $this->footballCalculator->calculateMatchTeamReferenceFrameAggregationAverage(
                $this->calculateMatchTeamReferenceFrameAggregations(
                    self::createMatchTeamReferenceFrameCriteria($cmp, homeTeam: true, awayTeam: false)
                )
            );
            $awayTeamAggregationAverage = $this->footballCalculator->calculateMatchTeamReferenceFrameAggregationAverage(
                $this->calculateMatchTeamReferenceFrameAggregations(
                    self::createMatchTeamReferenceFrameCriteria($cmp, homeTeam: false, awayTeam: true)
                )
            );

            $matchIds = array_unique([...$homeTeamAggregationAverage->matchIds, ...$awayTeamAggregationAverage->matchIds]);
            sort($matchIds);

            $competitionIds = array_unique([...$homeTeamAggregationAverage->competitionIds, ...$awayTeamAggregationAverage->competitionIds]);
            sort($competitionIds);

            $teamIds = array_unique([...$homeTeamAggregationAverage->teamIds, ...$awayTeamAggregationAverage->teamIds]);
            sort($teamIds);

            return new FootballCompetitionAverageGoalsForPerFulltime(
                matchIds: $matchIds,
                competitionIds: $competitionIds,
                teamIds: $teamIds,
                homeTeam: $homeTeamAggregationAverage->goalsForPerFulltime,
                awayTeam: $awayTeamAggregationAverage->goalsForPerFulltime
            );
        }

        $aggregationAverage = $this->footballCalculator->calculateMatchTeamReferenceFrameAggregationAverage(
            $aggregations
        );

        return new FootballCompetitionAverageGoalsForPerFulltime(
            matchIds: $aggregationAverage->matchIds,
            competitionIds: $aggregationAverage->competitionIds,
            teamIds: $aggregationAverage->teamIds,
            homeTeam: $aggregationAverage->goalsForPerFulltime,
            awayTeam: $aggregationAverage->goalsForPerFulltime
        );
    }

    /**
     * @param FootballMatch[] $matches
     * @param FootballCompetitionAverageGoalsForPerFulltime $competitionAverageGoalsForPerFulltime
     * @param FootballMatchTeamReferenceFrameAggregation[] $aggregations
     * @return array<string, FootballMatchXG>
     * @throws SerializerExceptionInterface
     */
    private function calculateMatchXGsFromTeamStrengths(
        array                                         $matches,
        FootballCompetitionAverageGoalsForPerFulltime $competitionAverageGoalsForPerFulltime,
        array                                         $aggregations
    ): array
    {
        return $this->footballCalculator->calculateMatchXGsFromTeamStrengths(
            $matches,
            $competitionAverageGoalsForPerFulltime,
            $this->footballCalculator->calculateTeamStrengths(
                $aggregations,
                $this->footballCalculator->calculateMatchTeamReferenceFrameAggregationAverage($aggregations)
            )
        );
    }
}
