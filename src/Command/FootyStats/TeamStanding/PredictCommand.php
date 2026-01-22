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
use App\Console\Command\DisplayTableDataTrait;
use App\Console\Command\FootyStats\AbstractTargetCommand as Command;
use App\Console\Command\FootyStats\PrettyTeamStandingsTrait;
use App\Console\Command\PrettyOptionTrait;
use App\Database\FootyStats\TeamStandingViewAwareTrait;
use App\Formatter\OrdinalNumberFormatterAwareTrait;
use App\Simulator\FootyStats\MatchesSimulator;
use App\Simulator\FootyStats\TeamStandingPositionDistributionsSimulator;
use Doctrine\DBAL\Exception as DBALException;
use JsonException;
use LogicException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Service\Attribute\Required;

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
        DisplayTableDataTrait,
        OrdinalNumberFormatterAwareTrait,
        PrettyOptionTrait,
        PrettyTeamStandingsTrait,
        TeamStandingsCalculatorAwareTrait,
        TeamStandingViewAwareTrait;

    private const int NUM_RUNS = 10000;

    private MatchesSimulator $matchesSimulator;
    private TeamStandingPositionDistributionsSimulator $teamStandingPositionDistributionsSimulator;

    private array $rulesConfig;

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

    #[Required]
    public function setRulesConfig(#[Autowire(param: 'app.footy_stats.rules')] array $rulesConfig): void
    {
        $this->rulesConfig = $rulesConfig;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'distribution',
                mode: InputOption::VALUE_NONE,
                description: 'Output team standing position distributions'
            )
            ->addOption(
                'relegation',
                mode: InputOption::VALUE_NONE,
                description: 'Only output relegation predictions'
            );

        $this
            ->configureCommandPrettyOption()
            ->configureCommandDataOptions();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws DBALException
     * @throws JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $this->getTargetArguments($input);
        $dataOutputOptions = $this->getCommandDataOptions($input);

        $isDistribution = $input->getOption('distribution');
        $isRelegation = $input->getOption('relegation');

        $rules = $this->rulesConfig['nation_competitions'][$target->nation][$target->competition] ?? [];

        if (!$isDistribution) {
            $initialTeamStandings = $this->footyStatsTeamStandingView
                ->createSelectQueryBuilder($target)
                ->select('*')
                ->fetchAllAssociative();

            $simulatedMatches = [];

            $this->matchesSimulator->simulate($target, 1, function ($matches) use (&$simulatedMatches) {
                $simulatedMatches = $matches;
            });

            $teamStandings = $this->teamStandingsCalculator->calculate(
                $simulatedMatches,
                $initialTeamStandings
            );

            if ($isRelegation) {
                $relegationPositions = $rules['relegation_positions'] ?? [];

                $teamStandings = array_values(
                    array_filter($teamStandings, fn (array $t) => in_array($t['position'], $relegationPositions))
                );
            }

            if (empty($teamStandings)) {
                throw new LogicException('Expected simulated team standings');
            }

            if ($this->getCommandPrettyOption($input)) {
                $teamStandings = self::prettifyFootyStatsTeamStandings($teamStandings);
            }

            $this->displayCommandTableData($teamStandings, $dataOutputOptions);

            return Command::SUCCESS;
        }

        $showProgress = !$dataOutputOptions['json'] && !$dataOutputOptions['csv'];

        if ($showProgress) {
            $this->io->progressStart(self::NUM_RUNS);
        }

        $teamStandingPositionDistributions = $this->teamStandingPositionDistributionsSimulator->simulate(
            $target,
            self::NUM_RUNS,
            function () use ($showProgress) {
                if ($showProgress) {
                    $this->io->progressAdvance();
                }
            }
        );

        if ($showProgress) {
            $this->io->progressFinish();
        }

        if ($isRelegation) {
            $teamStandingPositionDistributions = array_map(
                fn (array $d) => [
                    'team_name' => $d['team_name'],
                    'relegation_chance' => array_reduce(
                        $rules['relegation_positions'],
                        fn (float $c, float $p) => $c + $d[$this->ordinalNumberFormatter->format($p)],
                        0
                    )
                ],
                $teamStandingPositionDistributions
            );
        }

        if (empty($teamStandingPositionDistributions)) {
            throw new LogicException('Expected simulated team standing position distributions');
        }

        if ($this->getCommandPrettyOption($input)) {
            $teamStandingPositionDistributions = array_map(
                function (array $distribution) {
                    $prettyDistribution = [];

                    foreach ($distribution as $column => $value) {
                        if ($column === 'team_name') {
                            $prettyDistribution['Team'] = $value;
                            continue;
                        }

                        if ($column === 'relegation_chance') {
                            $column = 'Relegation Chance';
                        }

                        $prettyDistribution[$column] = number_format(round(100 * $value, 2), 2) . '%';
                    }

                    return $prettyDistribution;
                },
                $teamStandingPositionDistributions
            );
        }

        $this->displayCommandTableData($teamStandingPositionDistributions, $dataOutputOptions);

        return Command::SUCCESS;
    }
}
