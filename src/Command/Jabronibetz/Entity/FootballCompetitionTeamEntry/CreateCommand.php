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

namespace App\Command\Jabronibetz\Entity\FootballCompetitionTeamEntry;

use App\Domain\Jabronibetz\Console\Command\Command;
use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballCompetitionTeamEntry;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:jabronibetz:entity:football-competition-team-entry:create',
    description: 'Create a football competition team entry'
)]
final class CreateCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'competition-id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football competition that a team is entering'
            )
            ->addArgument(
                name: 'team-id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football team entering the competition'
            )
            ->addOption(
                name: 'group',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The group in which the football team begins the competition'
            )
            ->addOption(
                name: 'result',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The result of the football team in the competition'
            )
            ->addOption(
                name: 'seed',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The seed of the football team in the competition'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to create a
                <comment>football competition team entry</comment> in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <competition-id> <team-id> [--group <group>] [--result <result>] [--seed <seed>]</info>

                Examples:
                  <info>%command.full_name% 1 1 --group A --seed 14</info>

                If no competition id or team id is specified, you'll be prompted interactively.
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
        $this->interactFootballCompetition($input, $output, 'competition-id', 'Football competition: ');
        $this->interactFootballTeam($input, $output, 'team-id', 'Football team: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Jabronibetz: Create Football Competition Team Entry');

        try {
            $entry = (new FootballCompetitionTeamEntry())
                ->setCompetition($this->parseFootballCompetitionArgument($input, 'competition-id'))
                ->setTeam($this->parseFootballTeamArgument($input, 'team-id'))
                ->setGroup($this->parseStringOption($input, 'group', true))
                ->setResult($this->parseStringOption($input, 'result', true))
                ->setSeed($this->parseIntOption($input, 'seed'));

            $this->validate($entry);

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $entry,
                    [
                        AbstractNormalizer::GROUPS => [
                            FootballCompetitionTeamEntry::GROUP_DETAIL,
                            FootballCompetition::GROUP_LIST,
                            FootballTeam::GROUP_LIST
                        ]
                    ]
                ));

                if (!$io->confirm('Create football competition team entry?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($entry);
            $this->entityManager->flush();

            $io->success(sprintf('Football competition team entry has been created with id %d.', $entry->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
