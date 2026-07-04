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
    name: 'app:jabronibetz:entity:football-competition-team-entry:update',
    description: 'Update a football competition team entry'
)]
final class UpdateCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football competition team entry'
            )
            ->addOption(
                name: 'competition-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The id of the football competition that a team entered'
            )
            ->addOption(
                name: 'team-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The id of the football team that entered the competition'
            )
            ->addOption(
                name: 'group',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The group in which the football team began the competition',
                default: false
            )
            ->addOption(
                name: 'result',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The result of the football team in the competition',
                default: false
            )
            ->addOption(
                name: 'seed',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The seed of the football team in the competition',
                default: false
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to update a
                <comment>football competition team entry</comment> in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <id> [--competition-id <competition-id>] [--team-id <team-id>]
                    [--group [<group>] [--result [<result>]] [--seed [<seed>]]</info>

                Examples:
                  <info>%command.full_name% 1 --result Winners</info>

                If no id is specified, you'll be prompted interactively.
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
        $this->interactFootballCompetitionTeamEntry($input, $output, 'id', 'Football competition team entry: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Jabronibetz: Update Football Competition Team Entry');

        try {
            $entry = $this->parseFootballCompetitionTeamEntryArgument($input, 'id');

            $entry->setCompetition($this->parseFootballCompetitionOption($input, 'competition-id') ?? $entry->getCompetition());
            $entry->setTeam($this->parseFootballTeamArgument($input, 'team-id') ?? $entry->getTeam());

            $group = $this->parseStringOption($input, 'group', true);
            $entry->setGroup($group === false ? $entry->getGroup() : $group);

            $result = $this->parseStringOption($input, 'result', true);
            $entry->setResult($result === false ? $entry->getResult() : $result);

            $seed = $this->parseIntOption($input, 'seed');
            $entry->setSeed($seed === false ? $entry->getSeed() : $seed);

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

                if (!$io->confirm('Update football competition team entry?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($entry);
            $this->entityManager->flush();

            $io->success(sprintf('Football competition team entry with id %d has been updated.', $entry->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
