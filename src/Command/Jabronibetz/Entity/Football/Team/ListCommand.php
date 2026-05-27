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

namespace App\Command\Jabronibetz\Entity\Football\Team;

use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\Repository\FootballTeamRepository;
use App\Domain\Shared\Console\Style\DefinitionListConverter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:jabronibetz:football:team:list',
    description: 'List football teams',
    aliases: ['app:jbetz:footy:team:list'],
)]
final class ListCommand extends Command
{
    /**
     * @param FootballTeamRepository $footballTeamRepository
     * @param DefinitionListConverter $definitionListConverter
     */
    public function __construct(
        private readonly FootballTeamRepository  $footballTeamRepository,
        private readonly DefinitionListConverter $definitionListConverter
    )
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setHelp(
                <<<'HELP'
                The <info>%command.name%</info> command allows you to list
                <comment>football team</comment>s in the <comment>Jabronibetz</comment> db.

                Usage:
                  <info>%command.full_name%</info>

                Examples:
                  <info>%command.full_name%</info>
                HELP
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
        $io->title('Jabronibetz: Football Team List');

        try {
            $teams = $this->footballTeamRepository->findAll();
            foreach ($teams as $team) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $team,
                    [
                        AbstractNormalizer::GROUPS => FootballTeam::GROUP_LIST
                    ]
                ));
            }
            $io->info(sprintf('Found %d football teams.', count($teams)));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
