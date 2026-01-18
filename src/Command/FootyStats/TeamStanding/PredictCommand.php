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

namespace App\Command\FootyStats\TeamStanding;

use App\Calculator\FootyStats\TeamStandingsCalculatorAwareTrait;
use App\Console\Command\DataOptionsTrait;
use App\Console\Command\FootyStats\AbstractCommand as Command;
use App\Console\Command\FootyStats\PrettyTeamStandingsTrait;
use App\Console\Command\PrettyOptionTrait;
use App\Database\FootyStats\TeamStandingViewAwareTrait;
use App\Simulator\FootyStats\MatchesSimulator;
use App\Simulator\FootyStats\TeamStandingPositionDistributionsSimulator;
use LogicException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:team-standing:predict',
    description: 'Predict team standings',
)]
final class PredictCommand extends Command
{
    use DataOptionsTrait,
        PrettyOptionTrait,
        PrettyTeamStandingsTrait,
        TeamStandingsCalculatorAwareTrait,
        TeamStandingViewAwareTrait;

    private const int NUM_RUNS = 10000;

    private MatchesSimulator $matchesSimulator;
    private TeamStandingPositionDistributionsSimulator $teamStandingPositionDistributionsSimulator;

    #[Required]
    public function setMatchesSimulator(MatchesSimulator $simulator): void
    {
        $this->matchesSimulator = $simulator;
    }

    #[Required]
    public function setTeamStandingPositionDistributionsSimulator(TeamStandingPositionDistributionsSimulator $simulator): void
    {
        $this->teamStandingPositionDistributionsSimulator = $simulator;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'distribution',
                mode: InputOption::VALUE_NONE,
                description: 'Output team standing position distributions'
            );

        $this
            ->configurePrettyOption()
            ->configureDataOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $this->getTargetArguments($input);
        $dataOutputOptions = $this->getDataOptions($input);

        $isDistribution = $input->getOption('distribution');

        if (!$isDistribution) {
            try {
                $initialTeamStandings = $this->teamStandingView
                    ->createSelectQueryBuilder($target)
                    ->select('*')
                    ->fetchAllAssociative();
            } catch (Throwable $e) {
                throw new RuntimeException('Error getting initial team standings', previous: $e);
            }

            try {
                $simulatedMatches = [];

                $this->matchesSimulator->simulate($target, 1, function ($matches) use (&$simulatedMatches) {
                    $simulatedMatches[] = $matches;
                });
            } catch (Throwable $e) {
                throw new RuntimeException('Error simulating pending matches', previous: $e);
            }

            $teamStandings = $this->teamStandingsCalculator->calculate(
                $simulatedMatches,
                $initialTeamStandings
            );

            if (empty($teamStandings)) {
                throw new LogicException('Expected simulated team standings');
            }

            if ($this->getPrettyOption($input)) {
                $teamStandings = self::prettifyTeamStandings($teamStandings);
            }

            $columns = array_keys($teamStandings[0]);

            try {
                if ($dataOutputOptions['json']) {
                    $this->io->json($teamStandings);
                } else if ($dataOutputOptions['csv']) {
                    $this->io->csv($columns, $teamStandings);
                } else {
                    $this->io->table($columns, $teamStandings);
                }
            } catch (Throwable $e) {
                throw new RuntimeException('Error displaying simulated team standings', previous: $e);
            }

            return Command::SUCCESS;
        }

        $showProgress = !$dataOutputOptions['json'] && !$dataOutputOptions['csv'];

        if ($showProgress) {
            $this->io->progressStart(self::NUM_RUNS);
        }

        try {
            $teamStandingPositionDistributions = $this->teamStandingPositionDistributionsSimulator->simulate(
                $target,
                self::NUM_RUNS,
                function () use ($showProgress) {
                    if ($showProgress) {
                        $this->io->progressAdvance();
                    }
                }
            );
        } catch (Throwable $e) {
            throw new RuntimeException('Error simulating team standing position distributions', previous: $e);
        }

        if ($showProgress) {
            $this->io->progressFinish();
        }

        if (empty($teamStandingPositionDistributions)) {
            throw new LogicException('Expected simulated team standing position distributions');
        }

        if ($this->getPrettyOption($input)) {
            $teamStandingPositionDistributions = array_map(
                function (array $distribution) {
                    $prettyDistribution = [];

                    foreach ($distribution as $column => $value) {
                        if ($column === 'team_name') {
                            $prettyDistribution['Team'] = $value;
                            continue;
                        }

                        $prettyDistribution[$column] = number_format(round(100 * $value, 2), 2) . '%';
                    }

                    return $prettyDistribution;
                },
                $teamStandingPositionDistributions
            );
        }

        $columns = array_keys($teamStandingPositionDistributions[0]);

        try {
            if ($dataOutputOptions['json']) {
                $this->io->json($teamStandingPositionDistributions);
            } else if ($dataOutputOptions['csv']) {
                $this->io->csv($columns, $teamStandingPositionDistributions);
            } else {
                $this->io->table($columns, $teamStandingPositionDistributions);
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Error displaying simulated team standing position distributions', previous: $e);
        }

        return Command::SUCCESS;
    }
}
