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

namespace App\Command\FootyStats\Match\Chance;

use App\Calculator\FootyStats\MatchChancesCalculatorAwareTrait;
use App\Console\Command\Command;
use App\Console\Command\DataOptionsTrait;
use App\Console\Command\FootyStats\TargetArgumentsTrait;
use App\Database\FootyStats\MatchTableAwareTrait;
use LogicException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:match:chance:list',
    description: 'List (pending) match chances',
)]
final class ListCommand extends Command
{
    use DataOptionsTrait, MatchChancesCalculatorAwareTrait, MatchTableAwareTrait, TargetArgumentsTrait;

    protected function configure(): void
    {
        $this->configureTargetArguments()
            ->addOption('scores', mode: InputOption::VALUE_NONE, description: 'Include scoreline chances')
            ->addOption('pretty', mode: InputOption::VALUE_NONE, description: 'Output with formatting');
        $this->configureDataOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $this->getTargetArguments($input);
        $dataOutputOptions = $this->getDataOptions($input);

        $isScores = $input->getOption('scores');

        try {
            $pendingMatches = $this->matchTable
                ->createSelectQueryBuilder($target)
                ->select('*')
                ->where('home_team_score IS NULL')
                ->orderBy('timestamp')
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new RuntimeException('Error getting pending matches', previous: $e);
        }

        if (empty($pendingMatches)) {
            if ($dataOutputOptions['json']) {
                $this->io->writeln('[]');
            }

            return Command::SUCCESS;
        }

        try {
            $matchChancesIndex = $this->matchChancesCalculator->calculate($target);
        } catch (Throwable $e) {
            throw new RuntimeException('Error calculating match chances', previous: $e);
        }

        if (empty($matchChancesIndex)) {
            throw new LogicException('Expected match chances');
        }

        $matchChances = [];

        foreach ($pendingMatches as $pendingMatch) {
            $homeTeamName = $pendingMatch['home_team_name'];
            $awayTeamName = $pendingMatch['away_team_name'];

            $matchResultChances = $matchChancesIndex[$homeTeamName][$awayTeamName];

            $matchChances[] = [
                'home_team_name' => $homeTeamName,
                'away_team_name' => $awayTeamName,
                'home_team_win_chance' => array_sum(array_values($matchResultChances[0])),
                'draw_chance' => array_sum(array_values($matchResultChances[1])),
                'away_team_win_chance' => array_sum(array_values($matchResultChances[2])),
                'timestamp' => $pendingMatch['timestamp'],
                'extra' => $pendingMatch['extra'],
                ...($isScores
                    ? [...$matchResultChances[0], ...$matchResultChances[1], ...$matchResultChances[2]]
                    : [])

            ];
        }

        if ($input->getOption('pretty')) {
            $matchChances = array_map(
                function (array $matchChances) {
                    $prettyMatchChances = [];

                    foreach ($matchChances as $column => $value) {
                        switch ($column) {
                            case 'home_team_name':
                                $prettyMatchChances['Home'] = $value;
                                break;
                            case 'away_team_name':
                                $prettyMatchChances['Away'] = $value;
                                break;
                            case 'home_team_win_chance':
                                $prettyMatchChances['Home Win'] = number_format(round(100 * $value, 2), 2) . '%';
                                break;
                            case 'draw_chance':
                                $prettyMatchChances['Draw'] = number_format(round(100 * $value, 2), 2) . '%';
                                break;
                            case 'away_team_win_chance':
                                $prettyMatchChances['Away Win'] = number_format(round(100 * $value, 2), 2) . '%';
                                break;
                            case 'timestamp':
                                $prettyMatchChances['Timestamp'] = date('Y-m-d H:i:s T', $value);
                                break;
                            case 'extra':
                                $prettyMatchChances['Extra'] = $value;
                                break;
                            default:
                                $prettyMatchChances[$column] = number_format(round(100 * $value, 2), 2) . '%';
                                break;
                        }
                    }

                    return $prettyMatchChances;
                },
                $matchChances
            );
        }

        $columns = array_keys($matchChances[0]);

        try {
            if ($dataOutputOptions['json']) {
                $this->io->json($matchChances);
            } else if ($dataOutputOptions['csv']) {
                $this->io->csv($columns, $matchChances);
            } else {
                $this->io->table($columns, $matchChances);
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Error displaying match chances', previous: $e);
        }

        return Command::SUCCESS;
    }
}
