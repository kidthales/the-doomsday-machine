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
use App\Domain\Jabronibetz\DTO\FootballTeamStrength;
use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballMatchTeamReferenceFrame;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
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
    name: 'app:jabronibetz:football-team-strength:list',
    description: 'List football team strengths for given competition'
)]
final class FootballTeamStrengthListCommand extends Command
{
    use EntityManagerAwareTrait, FootballCalculatorAwareTrait;

    private const array HEADERS = ['Team', 'Attack', 'Defense'];

    /**
     * @param FootballCompetition $cmp
     * @param array $teams
     * @param int $groupRounds
     * @return Criteria
     */
    private static function createMatchTeamReferenceFrameCriteria(
        FootballCompetition $cmp,
        array               $teams = [],
        int                 $groupRounds = 0
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
                description: 'List & calculate football team strengths by competition group'
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
        $io->title('Jabronibetz: List Football Team Strengths');

        try {
            $cmp = $this->entityManager->find(FootballCompetition::class, $input->getArgument('competition-id'));
            if ($cmp === null) {
                $io->error('Football competition not found');
                return Command::FAILURE;
            }

            $io->section($cmp->getName());

            if ($input->getOption('group')) {
                $groupRounds = $cmp->getGroupRounds();
                if ($groupRounds === null) {
                    $io->error('Football competition does not have a group phase');
                    return Command::FAILURE;
                }

                foreach ($cmp->getTeamsByGroup() as $group => $teams) {
                    $teamStrengths = $this->calculateTeamStrengths(
                        self::createMatchTeamReferenceFrameCriteria($cmp, $teams->toArray(), $groupRounds)
                    );
                    foreach ($teams as $team) {
                        $teamId = (string)$team->getId();
                        if (!isset($teamStrengths[$teamId])) {
                            $teamStrengths[$teamId] = new FootballTeamStrength([], [$cmp->getId()], (int)$teamId, 0, 0);
                        }
                    }
                    $table = new Table($output);
                    $table->setHeaderTitle(sprintf('Group %s', $group));
                    $table->setHeaders(self::HEADERS);
                    $table->setRows($this->formatTeamStrengths($teamStrengths));
                    $table->render();
                }
            } else {
                $teamStrengths = $this->calculateTeamStrengths(self::createMatchTeamReferenceFrameCriteria($cmp));
                foreach ($cmp->getTeamEntries() as $entry) {
                    $teamId = (string)$entry->getTeam()->getId();
                    if (!isset($teamStrengths[$teamId])) {
                        $teamStrengths[$teamId] = new FootballTeamStrength([], [$cmp->getId()], (int)$teamId, 0, 0);
                    }
                }
                $io->table(self::HEADERS, $this->formatTeamStrengths($teamStrengths));
            }
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param Criteria $criteria
     * @return array<string, FootballTeamStrength>
     * @throws SerializerExceptionInterface
     */
    private function calculateTeamStrengths(Criteria $criteria): array
    {
        $aggregations = $this->footballCalculator->calculateMatchTeamReferenceFrameAggregations(
            $this->entityManager
                ->getRepository(FootballMatchTeamReferenceFrame::class)
                ->matching($criteria)
                ->toArray()
        );
        return $this->footballCalculator->calculateTeamStrengths(
            $aggregations,
            $this->footballCalculator->calculateMatchTeamReferenceFrameAggregationAverage($aggregations)
        );
    }

    /**
     * @param FootballTeamStrength[] $teamStrengths
     * @return array
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function formatTeamStrengths(array $teamStrengths): array
    {
        $formattedTeamStrengths = array_reduce(
            $teamStrengths,
            function (array $rows, FootballTeamStrength $teamStrength) {
                $rows[] = [
                    $this->entityManager->find(FootballTeam::class, $teamStrength->teamId)?->getName() ?? 'Unknown',
                    $teamStrength->attack,
                    $teamStrength->defense
                ];
                return $rows;
            },
            []
        );
        usort($formattedTeamStrengths, fn(array $a, array $b) => strcmp($a[0], $b[0]));
        return $formattedTeamStrengths;
    }
}
