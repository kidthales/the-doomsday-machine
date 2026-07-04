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
use App\Domain\Jabronibetz\Entity\FootballOrganization;
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
    name: 'app:jabronibetz:entity:football-competition:update',
    description: 'Update a football competition'
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
                description: 'The id of the football competition'
            )
            ->addOption(
                name: 'name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The name of the football competition'
            )
            ->addOption(
                name: 'short-name',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The short name of the football competition'
            )
            ->addOption(
                name: 'organization-id',
                mode: InputOption::VALUE_REQUIRED,
                description: 'The id of the football organization managing this competition'
            )
            ->addOption(
                name: 'rounds',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The total football match rounds for this competition',
                default: false
            )
            ->addOption(
                name: 'group-rounds',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'The total football match rounds for this competition\'s group phase',
                default: false
            )
            ->addOption(
                name: 'separate-match-xg-home-away',
                mode: InputOption::VALUE_OPTIONAL,
                description: 'Flag if separate football match xg calculations are used for home and away teams',
                default: '~'
            )
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to update a <comment>football competition</comment>
                in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name% <id>
                    [--name <name>] [--short-name <short-name>] [--organization-id <organization-id>]
                    [--rounds [<rounds>]] [--group-rounds [<group-rounds>]]
                    [--separate-match-xg-home-away [<separate-match-xg-home-away>]]</info>

                Examples:
                  <info>%command.full_name% 1 --short-name THIEFA</info>

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
        $this->interactFootballCompetition($input, $output, 'id', 'Football competition: ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Jabronibetz: Update Football Competition');

        try {
            $competition = $this->parseFootballCompetitionArgument($input, 'id');

            $competition->setName($this->parseStringOption($input, 'name', true) ?? $competition->getName());
            $competition->setShortName(
                $this->parseStringOption($input, 'short-name', true) ?? $competition->getShortName()
            );
            $competition->setManagingOrganization(
                $this->parseFootballOrganizationOption($input, 'organization-id') ?? $competition->getManagingOrganization()
            );

            $rounds = $this->parseIntOption($input, 'rounds');
            $competition->setRounds($rounds === false ? $competition->getRounds() : $rounds);

            $groupRounds = $this->parseIntOption($input, 'group-rounds');
            $competition->setGroupRounds($groupRounds === false ? $competition->getGroupRounds() : $groupRounds);

            $separateMatchXGHomeAway = $this->parseBoolOption($input, 'separate-match-xg-home-away');
            $competition->setSeparateMatchXgHomeAway(
                $separateMatchXGHomeAway === '~' ? $competition->getSeparateMatchXgHomeAway() : $separateMatchXGHomeAway
            );

            $this->validate($competition);

            if ($input->isInteractive()) {
                $io->section('Confirmation');
                $io->definitionList(...$this->definitionListConverter->convert(
                    $competition,
                    [
                        AbstractNormalizer::GROUPS => FootballCompetition::GROUP_DETAIL
                    ]
                ));

                if (!$io->confirm('Update football competition?')) {
                    return Command::SUCCESS;
                }
            }

            $this->entityManager->persist($competition);
            $this->entityManager->flush();

            $io->success(sprintf('Football competition with id %d has been updated.', $competition->getId()));
        } catch (Throwable $e) {
            $this->logThrowable($e);
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
