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

namespace App\Command\FootyStats\Data;

use App\Command\FootyStats\Trait\TargetOptionChoiceTrait;
use App\FootyStats\Database\MatchTableAwareTrait;
use App\FootyStats\ScraperAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:data:diff',
    description: 'Insert or update Footy Stats table data'
)]
final class DiffCommand extends Command
{
    use MatchTableAwareTrait, ScraperAwareTrait, TargetOptionChoiceTrait;

    protected function configure(): void
    {
        $this->configureTargetOptionChoice();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Diff Footy Stats Data');

        $target = $this->promptTargetOptionChoice($input);
        $this->io->info((string)$target);

        if (!$this->matchTable->exists($target)) {
            $this->io->error([
                'Match table not found',
                'Please run app:footy-stats:migrations:generate for ' . $target,
                'Always review the generated migration before applying it'
            ]);

            return Command::FAILURE;
        }

        $teamNameIndex = [];
        foreach ($this->scraper->scrapeTeamNames($target) as $teamNames) {
            $teamNameIndex[$teamNames[1]] = $teamNames[0];
        }

        $selectQueryBuilder = $this->matchTable
            ->createSelectQueryBuilder($target)
            ->select('*');

        $inserts = [];
        $updates = [];

        foreach ($this->scraper->scrapeMatches($target) as $scrapedMatch) {
            $selectQueryBuilder->resetWhere();

            $dbMatch = $selectQueryBuilder
                ->where('home_team_name = :home_team_name')
                ->andWhere('away_team_name = :away_team_name')
                ->setParameter('home_team_name', $scrapedMatch['home_team_name'])
                ->setParameter('away_team_name', $scrapedMatch['away_team_name'])
                ->fetchAssociative();

            if ($dbMatch === false) {
                $inserts[] = $scrapedMatch;
                continue;
            }

            $candidate = [
                'home_team_name' => $dbMatch['home_team_name'],
                'away_team_name' => $dbMatch['away_team_name'],
            ];

            foreach ($dbMatch as $key => $value) {
                if (in_array($key, ['home_team_name', 'away_team_name'])) {
                    continue;
                }

                if ($value !== $scrapedMatch[$key]) {
                    $candidate[$key] = $scrapedMatch[$key];
                }
            }

            if (count($candidate) > 2) {
                $updates[] = $candidate;
            }
        }

        if (empty($inserts) && empty($updates)) {
            $this->io->success('No data changes detected');
            return Command::SUCCESS;
        }

        if (!empty($inserts)) {
            $this->io->section('Insert');
            $this->io->table(array_keys($inserts[0]), $inserts);
        }

        if (!empty($updates)) {
            $this->io->section('Update');

            $definitionList = [];
            foreach ($updates as $update) {
                foreach ($update as $key => $value) {
                    $definitionList[] = [$key => $value];
                }
                $definitionList[] = new TableSeparator();
            }
            $this->io->definitionList($definitionList);
        }

        if (!$this->io->confirm('Proceed with the data changes?')) {
            return Command::SUCCESS;
        }

        $this->io->info('Backup table: ' . $this->matchTable->backup($target));

        $this->io->progressStart(count($inserts) + count($updates));

        $insertQueryBuilder = $this->matchTable
            ->createInsertQueryBuilder($target)
            ->values([
                'home_team_name' => '?',
                'away_team_name' => '?',
                'home_team_score' => '?',
                'away_team_score' => '?',
                'timestamp' => '?',
                'extra' => '?'
            ]);

        foreach ($inserts as $insert) {
            $insertQueryBuilder
                ->setParameters([
                    $insert['home_team_name'],
                    $insert['away_team_name'],
                    $insert['home_team_score'],
                    $insert['away_team_score'],
                    $insert['timestamp'],
                    $insert['extra']
                ])
                ->executeStatement();

            $this->io->progressAdvance();
        }

        foreach ($updates as $update) {
            $updateQueryBuilder = $this->matchTable
                ->createUpdateQueryBuilder($target)
                ->where('home_team_name = :home_team_name')
                ->andWhere('away_team_name = :away_team_name')
                ->setParameter('home_team_name', $update['home_team_name'])
                ->setParameter('away_team_name', $update['away_team_name']);

            foreach ($update as $key => $value) {
                if (in_array($key, ['home_team_name', 'away_team_name'])) {
                    continue;
                }

                $updateQueryBuilder->set($key, ":$key");
                $updateQueryBuilder->setParameter($key, $value);
            }

            $updateQueryBuilder->executeStatement();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success(sprintf('Inserted %d rows. Updated %d rows', count($inserts), count($updates)));

        return Command::SUCCESS;
    }
}
