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

namespace App\Command\Jabronibetz\Entity\FootballCompetition;

use App\Domain\Jabronibetz\Console\Command\Command;
use App\Domain\Jabronibetz\Entity\FootballCompetition;
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
    name: 'app:jabronibetz:entity:football-competition:create',
    description: 'Create a football competition'
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
                name: 'name',
                mode: InputArgument::REQUIRED,
                description: 'The name of the football competition'
            )
            ->addArgument(
                name: 'short-name',
                mode: InputArgument::REQUIRED,
                description: 'The short name of the football competition'
            )
            ->addArgument(
                name: 'organization-id',
                mode: InputArgument::REQUIRED,
                description: 'The id of the football organization that manages the competition'
            )
            ->addOption(
                name: 'rounds',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The total football match rounds for the competition'
            )
            ->addOption(
                name: 'group-rounds',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The total football match rounds for the competition\'s group phase'
            )
            ->addOption(
                name: 'separate-match-xg-home-away',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Flag if separate football match xg calculations are used for home and away teams'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to create a <comment>football competition</comment>
                in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <name> <short-name> <organization-id>
                    [--rounds <rounds>] [--group-rounds <group-rounds>]
                    [--separate-match-xg-home-away <separate-match-xg-home-away>]</info>

                Examples:
                  <info>%command.full_name% "2026 FIFA World Cup" FWC26 1 --rounds 9 --group-rounds 3 --separate-match-xg-home-away false</info>

                If no name, short name, or organization id is specified, you'll be prompted interactively.
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
        $this->interactQuestion($input, $output, 'name', 'Football competition name: ');
        $this->interactQuestion($input, $output, 'short-name', 'Football competition short name: ');
        $this->interactFootballOrganization($input, $output, 'organization-id', 'Football competition managed by: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Jabronibetz: Create Football Competition');

        try {
            $competition = (new FootballCompetition())
                ->setName($this->parseStringArgument($input, 'name', true))
                ->setShortName($this->parseStringArgument($input, 'short-name', true))
                ->setManagingOrganization($this->parseFootballOrganizationArgument($input, 'organization-id'))
                ->setRounds($this->parseIntOption($input, 'rounds'))
                ->setGroupRounds($this->parseIntOption($input, 'group-rounds'))
                ->setSeparateMatchXgHomeAway($this->parseBoolOption($input, 'separate-match-xg-home-away'));

            $this->validate($competition);

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $competition,
                    [
                        AbstractNormalizer::GROUPS => FootballCompetition::GROUP_DETAIL
                    ]
                ));

                if (!$io->confirm('Create football competition?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($competition);
            $this->entityManager->flush();

            $io->success(sprintf('Football competition has been created with id %d.', $competition->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
