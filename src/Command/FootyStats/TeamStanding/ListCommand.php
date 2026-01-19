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

use App\Console\Command\DataOptionsTrait;
use App\Console\Command\FootyStats\AbstractCommand as Command;
use App\Console\Command\FootyStats\PrettyTeamStandingsTrait;
use App\Console\Command\PrettyOptionTrait;
use App\Database\FootyStats\AwayTeamStandingViewAwareTrait;
use App\Database\FootyStats\HomeTeamStandingViewAwareTrait;
use App\Database\FootyStats\TeamStandingViewAwareTrait;
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
    name: 'app:footy-stats:team-standing:list',
    description: 'List team standings',
)]
final class ListCommand extends Command
{
    use AwayTeamStandingViewAwareTrait,
        DataOptionsTrait,
        HomeTeamStandingViewAwareTrait,
        PrettyOptionTrait,
        PrettyTeamStandingsTrait,
        TeamStandingViewAwareTrait;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption('home', mode: InputOption::VALUE_NONE, description: 'Output home team standings')
            ->addOption('away', mode: InputOption::VALUE_NONE, description: 'Output away team standings');

        $this
            ->configureCommandPrettyOption()
            ->configureCommandDataOptions();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $this->getTargetArguments($input);
        $dataOutputOptions = $this->getCommandDataOptions($input);

        $isHome = $input->getOption('home');
        $isAway = $input->getOption('away');

        if ($isHome && $isAway) {
            throw new RuntimeException("Only one of '--home' or '--away' may be specified");
        }

        try {
            if ($isHome) {
                $selectQueryBuilder = $this->footyStatsHomeTeamStandingView->createSelectQueryBuilder($target);
            } else if ($isAway) {
                $selectQueryBuilder = $this->footyStatsAwayTeamStandingView->createSelectQueryBuilder($target);
            } else {
                $selectQueryBuilder = $this->footyStatsTeamStandingView->createSelectQueryBuilder($target);
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

        if ($this->getCommandPrettyOption($input)) {
            $teamStandings = self::prettifyFootyStatsTeamStandings($teamStandings);
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
