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

namespace App\Command\FootyStats\Match\Xg;

use App\Command\DataOptionsTrait;
use App\Command\FootyStats\TargetArgumentsTrait;
use App\Console\Style\DataStyle;
use App\FootyStats\Database\MatchXgViewAwareTrait;
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
    name: 'app:footy-stats:match:xg:list',
    description: 'List (pending) match expected goals',
)]
final class ListCommand extends Command
{
    use DataOptionsTrait, MatchXgViewAwareTrait, TargetArgumentsTrait;

    private DataStyle $io;

    protected function configure(): void
    {
        $this->configureTargetArguments()
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

        try {
            $matchXgAll = $this->matchXgView
                ->createSelectQueryBuilder($target)
                ->select('*')
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new RuntimeException('Error getting all match expected goals', previous: $e);
        }

        if (empty($matchXgAll)) {
            if ($dataOutputOptions['json']) {
                $this->io->writeln('[]');
            }

            return Command::SUCCESS;
        }

        if ($input->getOption('pretty')) {
            $matchXgAll = array_map(
                fn (array $matchXg) => [
                    'Home' => $matchXg['home_team_name'],
                    'Away' => $matchXg['away_team_name'],
                    'Home XG' => number_format(round($matchXg['home_team_xg'], 2), 2),
                    'Away XG' => number_format(round($matchXg['away_team_xg'], 2), 2),
                    'Timestamp' => date('Y-m-d H:i:s T', $matchXg['timestamp']),
                    'Extra' => $matchXg['extra']
                ],
                $matchXgAll
            );
        }

        $columns = array_keys($matchXgAll[0]);

        try {
            if ($dataOutputOptions['json']) {
                $this->io->json($matchXgAll);
            } else if ($dataOutputOptions['csv']) {
                $this->io->csv($columns, $matchXgAll);
            } else {
                $this->io->table($columns, $matchXgAll);
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Error displaying all match expected goals', previous: $e);
        }

        return Command::SUCCESS;
    }
}
