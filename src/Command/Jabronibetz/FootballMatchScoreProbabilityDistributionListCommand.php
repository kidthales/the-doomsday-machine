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

use App\Domain\Jabronibetz\DataProvider\FootballCompetitionDataProviderAwareTrait;
use App\Domain\Jabronibetz\DTO\FootballMatchScoreProbabilityDistribution;
use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballMatch;
use App\Domain\Jabronibetz\ORM\EntityManagerAwareTrait;
use App\Domain\Shared\Console\Command\Command;
use App\Domain\Shared\Console\Style\DefinitionListConverterAwareTrait;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:jabronibetz:football-match-score-probability-distribution:list',
    description: 'List football match score probability distributions for given competition'
)]
final class FootballMatchScoreProbabilityDistributionListCommand extends Command
{
    use DefinitionListConverterAwareTrait,
        EntityManagerAwareTrait,
        FootballCompetitionDataProviderAwareTrait;

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
                description: 'List & calculate football match score probability distributions by competition group'
            )
            ->addOption(
                name: 'limit',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Limit quantity the of football match score probability distributions displayed'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to list the
                <comment>football match score probability distribution</comment>s for a <comment>football competition</comment>
                that exists in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <competition-id> [--group] [--limit <limit>]</info>

                Examples:
                  <info>%command.full_name% 1 --limit 32</info>

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

        try {
            $competition = $this->entityManager->find(FootballCompetition::class, $input->getArgument('competition-id'));
            if ($competition === null) {
                $io->error('Football competition not found');
                return Command::FAILURE;
            }

            $io->title(
                sprintf('Jabronibetz: List Football Match Score Probability Distributions - %s', $competition->getName())
            );

            $group = $input->getOption('group');
            $limit = $input->getOption('limit');
            if ($limit !== null) {
                if (!is_numeric($limit)) {
                    $io->error('Limit quantity must be a numeric value');
                    return Command::FAILURE;
                }
                $limit = intval($limit);
            }
            $matchScoreProbabilityDistributions = $this->footballCompetitionDataProvider
                ->getMatchScoreProbabilityDistributions($competition, $group, $limit);

            if ($group) {
                foreach ($matchScoreProbabilityDistributions as $matchScoreProbabilityDistributionGroup => $groupMatchScoreProbabilityDistributions) {
                    $io->section(sprintf('Group: %s', $matchScoreProbabilityDistributionGroup));
                    foreach ($groupMatchScoreProbabilityDistributions as $groupMatchScoreProbabilityDistribution) {
                        $this->displayMatchScoreProbabilityDistribution(
                            $output,
                            $io,
                            $groupMatchScoreProbabilityDistribution
                        );
                    }
                }
            } else {
                foreach ($matchScoreProbabilityDistributions as $matchScoreProbabilityDistribution) {
                    $this->displayMatchScoreProbabilityDistribution(
                        $output,
                        $io,
                        $matchScoreProbabilityDistribution
                    );
                }
            }
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @param FootballMatchScoreProbabilityDistribution $matchScoreProbabilityDistribution
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function displayMatchScoreProbabilityDistribution(
        OutputInterface                           $output,
        SymfonyStyle                              $io,
        FootballMatchScoreProbabilityDistribution $matchScoreProbabilityDistribution
    ): void
    {
        $match = $this->entityManager->find(FootballMatch::class, $matchScoreProbabilityDistribution->matchId);
        $table = new Table($output);
        $table->setHeaderTitle($match->getChoiceValue());
        $table->setHeaders([
            sprintf('%s \\ %s', $match->getHomeTeam()?->getName(), $match->getAwayTeam()?->getName()),
            ...array_keys($matchScoreProbabilityDistribution->distribution)
        ]);
        $table->setRows(
            array_map(
                fn(int $k, array $v) => [
                    sprintf('<info>%d</info>', $k),
                    ...array_map(
                        function (float $c) {
                            $bg = 'black';
                            if ($c >= 0.20) {
                                $bg = 'bright-green';
                            } else if ($c >= 0.125) {
                                $bg = 'green';
                            } else if ($c >= 0.083) {
                                $bg = 'bright-yellow';
                            } else if ($c >= 0.05) {
                                $bg = 'yellow';
                            } else if ($c >= 0.01) {
                                $bg = 'bright-red';
                            } else if ($c >= 0.00001) {
                                $bg = 'red';
                            }
                            // TODO
                            return sprintf('<bg=%s>%.2f%%</>', $bg, $c * 100);
                        },
                        $v
                    )
                ],
                array_keys($matchScoreProbabilityDistribution->distribution),
                array_values($matchScoreProbabilityDistribution->distribution)
            )
        );
        $table->render();

        $drawChance = 0;
        $homeWinChance = 0;
        $awayWinChance = 0;
        for ($h = 0; $h < count($matchScoreProbabilityDistribution->distribution); ++$h) {
            for ($a = 0; $a < count($matchScoreProbabilityDistribution->distribution[0]); ++$a) {
                if ($h === $a) {
                    $drawChance += $matchScoreProbabilityDistribution->distribution[$h][$a];
                } else if ($h > $a) {
                    $homeWinChance += $matchScoreProbabilityDistribution->distribution[$h][$a];
                } else {
                    $awayWinChance += $matchScoreProbabilityDistribution->distribution[$h][$a];
                }
            }
        }

        $io->definitionList(
            [sprintf('%s Win', $match->getHomeTeam()?->getName()) => sprintf('%.2f%%', $homeWinChance * 100)],
            ['Draw' => sprintf('%.2f%%', $drawChance * 100)],
            [sprintf('%s Win', $match->getAwayTeam()?->getName()) => sprintf('%.2f%%', $awayWinChance * 100)],
        );
    }
}
