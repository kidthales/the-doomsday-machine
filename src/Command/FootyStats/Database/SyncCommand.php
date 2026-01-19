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

namespace App\Command\FootyStats\Database;

use App\Console\Command\AbstractCommand as Command;
use App\Scraper\FootyStatsScraperAwareTrait;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:database:sync',
    description: 'Sync current Footy Stats seasons'
)]
final class SyncCommand extends Command
{
    use FootyStatsScraperAwareTrait;

    protected function configure(): void
    {
        $this
            ->addArgument('nation', InputArgument::OPTIONAL, 'Nation name')
            ->addArgument('competition', InputArgument::OPTIONAL, 'Competition name')
            ->addOption('all', mode: InputOption::VALUE_NONE, description: 'Sync all seasons');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Sync Footy Stats Database');

        $nation = $input->getArgument('nation');

        if (!empty($nation) && !in_array($nation, $this->footyStatsScraper->getNations())) {
            throw new RuntimeException(sprintf('Invalid nation argument: %s', $nation));
        }

        $competition = $input->getArgument('competition');

        if (empty($nation) && empty($competition)) {
            foreach ($this->footyStatsScraper->getNations() as $nation) {
                foreach ($this->footyStatsScraper->getCompetitions($nation) as $competition) {
                    $this->doDiff($nation, $competition, $input, $output);
                }
            }
        } else if (!empty($nation) && empty($competition)) {
            foreach ($this->footyStatsScraper->getCompetitions($nation) as $competition) {
                $this->doDiff($nation, $competition, $input, $output);
            }
        } else if (!empty($nation) && !empty($competition)) {
            if (!in_array($competition, $this->footyStatsScraper->getCompetitions($nation))) {
                throw new RuntimeException(sprintf('Invalid competition argument: %s', $competition));
            }

            $this->doDiff($nation, $competition, $input, $output);
        }

        return Command::SUCCESS;
    }

    private function doDiff(string $nation, string $competition, InputInterface $input, OutputInterface $output): void
    {
        $availableSeasons = $this->footyStatsScraper->scrapeAvailableSeasons($nation, $competition);

        $seasons = [$availableSeasons['current']];

        if ($input->getOption('all')) {
            $seasons = [...$seasons, ...array_keys($availableSeasons['previous']['overview'])];
            sort($seasons);
        }

        foreach ($seasons as $season) {
            $diffInput = new ArrayInput([
                'command' => 'app:footy-stats:database:diff',
                'nation' => $nation,
                'competition' => $competition,
                'season' => $season,
            ]);

            $diffInput->setInteractive($input->isInteractive());

            try {
                $this->getApplication()->doRun($diffInput, $output);
            } catch (Throwable) {
            }

            sleep(1);
        }
    }
}
