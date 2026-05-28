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

namespace App\Command\Jabronibetz\Entity\FootballMatch;

use App\Domain\Jabronibetz\Entity\FootballCompetition;
use App\Domain\Jabronibetz\Entity\FootballMatch;
use App\Domain\Jabronibetz\Entity\FootballTeam;
use App\Domain\Jabronibetz\Repository\FootballMatchRepository;
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
    name: 'app:jabronibetz:entity:football-match:list',
    description: 'List football matches'
)]
final class ListCommand extends Command
{
    /**
     * @param FootballMatchRepository $footballMatchRepository
     * @param DefinitionListConverter $definitionListConverter
     */
    public function __construct(
        private readonly FootballMatchRepository $footballMatchRepository,
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
                <comment>football match</comment> entities in the <comment>Jabronibetz</comment> db.

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
        $io->title('Jabronibetz: Football Match List');

        try {
            $matches = $this->footballMatchRepository->findAll();
            foreach ($matches as $match) {
                $io->definitionList(...$this->definitionListConverter->convert(
                    $match,
                    [
                        AbstractNormalizer::GROUPS => [
                            FootballMatch::GROUP_LIST,
                            FootballCompetition::GROUP_LIST,
                            FootballTeam::GROUP_LIST
                        ]
                    ]
                ));
            }
            $io->info(sprintf('Found %d football matches.', count($matches)));
        } catch (Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
