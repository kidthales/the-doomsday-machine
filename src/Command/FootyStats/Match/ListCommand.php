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

namespace App\Command\FootyStats\Match;

use App\Command\DataOptionsTrait;
use App\Command\FootyStats\TargetArgumentsTrait;
use App\Console\Style\DataStyle;
use App\FootyStats\Database\MatchTableAwareTrait;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:match:list',
    description: 'List matches',
)]
final class ListCommand extends Command
{
    use DataOptionsTrait, MatchTableAwareTrait, TargetArgumentsTrait;

    private DataStyle $io;

    protected function configure(): void
    {
        $this->configureTargetArguments()
            ->addOption('completed', mode: InputOption::VALUE_NONE, description: 'List completed matches')
            ->addOption('pending', mode: InputOption::VALUE_NONE, description: 'List pending matches')
            ->addOption('pretty', mode: InputOption::VALUE_NONE, description: 'Output with formatting');
        $this->configureDataOptions();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new DataStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $this->getTargetArguments($input);
        $dataOutputOptions = $this->getDataOptions($input);

        $isCompleted = $input->getOption('completed');
        $isPending = $input->getOption('pending');

        if ($isCompleted && $isPending) {
            throw new RuntimeException("Only one of '--completed' or '--pending' may be specified");
        }

        $selectQueryBuilder = $this->matchTable
            ->createSelectQueryBuilder($target)
            ->select('*')
            ->orderBy('timestamp');

        try {
            if ($isCompleted) {
                $selectQueryBuilder->where('home_team_score IS NOT NULL');
            } else if ($isPending) {
                $selectQueryBuilder->where('home_team_score IS NULL');
            }

            $matches = $selectQueryBuilder->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new RuntimeException('Error getting matches', previous: $e);
        }

        if (empty($matches)) {
            if ($dataOutputOptions['json']) {
                $this->io->writeln('[]');
            }

            return Command::SUCCESS;
        }

        if ($input->getOption('pretty')) {
            $matches = array_map(
                fn (array $match) => [
                    'Home' => $match['home_team_name'],
                    'Away' => $match['away_team_name'],
                    'Home Score' => $match['home_team_score'],
                    'Away Score' => $match['away_team_score'],
                    'Timestamp' => date('Y-m-d H:i:s T', $match['timestamp']),
                    'Extra' => $match['extra']
                ],
                $matches
            );
        }

        $columns = array_keys($matches[0]);

        try {
            if ($dataOutputOptions['json']) {
                $this->io->json($matches);
            } else if ($dataOutputOptions['csv']) {
                $this->io->csv($columns, $matches);
            } else {
                $this->io->table($columns, $matches);
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Error displaying matches', previous: $e);
        }

        return Command::SUCCESS;
    }
}
