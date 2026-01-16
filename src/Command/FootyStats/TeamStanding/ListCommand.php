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

use App\Command\DataOptionsTrait;
use App\Command\FootyStats\TargetArgumentsTrait;
use App\Console\Style\DataStyle;
use App\FootyStats\Database\AwayTeamStandingView;
use App\FootyStats\Database\HomeTeamStandingView;
use App\FootyStats\Database\TeamStandingViewAwareTrait;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:team-standing:list',
    description: 'List team standings',
)]
final class ListCommand extends Command
{
    use DataOptionsTrait, TargetArgumentsTrait, TeamStandingViewAwareTrait;

    private DataStyle $io;

    private HomeTeamStandingView $homeTeamStandingView;
    private AwayTeamStandingView $awayTeamStandingView;

    #[Required]
    public function setHomeTeamStandingView(HomeTeamStandingView $homeTeamStandingView): void
    {
        $this->homeTeamStandingView = $homeTeamStandingView;
    }

    public function setAwayTeamStandingView(AwayTeamStandingView $awayTeamStandingView): void
    {
        $this->awayTeamStandingView = $awayTeamStandingView;
    }

    protected function configure(): void
    {
        $this->configureTargetArguments()
            ->addOption('home', mode: InputOption::VALUE_NONE, description: 'Output home team standings')
            ->addOption('away', mode: InputOption::VALUE_NONE, description: 'Output away team standings')
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

        $isHome = $input->getOption('home');
        $isAway = $input->getOption('away');

        if ($isHome && $isAway) {
            throw new RuntimeException("Only one of '--home' or '--away' may be specified");
        }

        try {
            if ($isHome) {
                $selectQueryBuilder = $this->homeTeamStandingView->createSelectQueryBuilder($target);
            } else if ($isAway) {
                $selectQueryBuilder = $this->awayTeamStandingView->createSelectQueryBuilder($target);
            } else {
                $selectQueryBuilder = $this->teamStandingView->createSelectQueryBuilder($target);
            }

            $teamStandings = $selectQueryBuilder
                ->select('*')
                ->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new RuntimeException('Error reading team standings', previous: $e);
        }

        if (empty($teamStandings)) {
            if ($dataOutputOptions['json']) {
                $this->io->writeln('[]');
            }

            return Command::SUCCESS;
        }

        if ($input->getOption('pretty')) {
            $teamStandings = array_map(
                fn (array $teamStanding) => [
                    '#' => $teamStanding['position'],
                    'Team' => $teamStanding['team_name'],
                    'MP' => $teamStanding['matches_played'],
                    'W' => $teamStanding['wins'],
                    'D' => $teamStanding['draws'],
                    'L' => $teamStanding['losses'],
                    'GF' => $teamStanding['goals_for'],
                    'GA' => $teamStanding['goals_against'],
                    'GD' => $teamStanding['goal_difference'],
                    'Pts' => $teamStanding['points'],
                    'Last 5' => substr($teamStanding['sequence'], -5),
                    'PPG' => number_format(round($teamStanding['points_per_game'], 2), 2)
                ],
                $teamStandings
            );
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
            throw new RuntimeException('Error displaying team standings', previous: $e);
        }

        return Command::SUCCESS;
    }
}
