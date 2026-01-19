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

use App\Console\Command\FootyStats\AbstractCommand as Command;
use App\Database\FootyStats\AwayTeamStandingView;
use App\Database\FootyStats\AwayTeamStandingViewAwareTrait;
use App\Database\FootyStats\ConnectionAwareTrait;
use App\Database\FootyStats\HomeTeamStandingView;
use App\Database\FootyStats\HomeTeamStandingViewAwareTrait;
use App\Database\FootyStats\MatchTable;
use App\Database\FootyStats\MatchTableAwareTrait;
use App\Database\FootyStats\MatchXgView;
use App\Database\FootyStats\MatchXgViewAwareTrait;
use App\Database\FootyStats\TeamStandingView;
use App\Database\FootyStats\TeamStandingViewAwareTrait;
use App\Database\FootyStats\TeamStrengthView;
use App\Database\FootyStats\TeamStrengthViewAwareTrait;
use App\Provider\FootyStats\TargetArgumentsProviderInterface;
use App\Scraper\FootyStatsScraperAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:data:diff',
    description: 'Create or update Footy Stats table data'
)]
final class DiffCommand extends Command
{
    use AwayTeamStandingViewAwareTrait,
        ConnectionAwareTrait,
        FootyStatsScraperAwareTrait,
        HomeTeamStandingViewAwareTrait,
        MatchTableAwareTrait,
        MatchXgViewAwareTrait,
        TeamStandingViewAwareTrait,
        TeamStrengthViewAwareTrait;

    public function setTargetArgumentsProvider(
        #[Autowire(service: 'app.provider.footy_stats.scraper_target_arguments_provider')]
        TargetArgumentsProviderInterface $provider
    ): void
    {
        parent::setTargetArgumentsProvider($provider);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Diff Footy Stats Data');

        $target = $this->getTargetArguments($input);
        $this->io->info((string)$target);

        $creates = [];

        if (!$this->footyStatsMatchTable->exists($target)) {
            $creates[] = MatchTable::getCreateSql($target);
        }

        if (!$this->footyStatsTeamStandingView->exists($target)) {
            $creates[] = TeamStandingView::getCreateSql($target);
        }

        if (!$this->footyStatsHomeTeamStandingView->exists($target)) {
            $creates[] = HomeTeamStandingView::getCreateSql($target);
        }

        if (!$this->footyStatsAwayTeamStandingView->exists($target)) {
            $creates[] = AwayTeamStandingView::getCreateSql($target);
        }

        if (!$this->footyStatsTeamStrengthView->exists($target)) {
            $creates[] = TeamStrengthView::getCreateSql($target);
        }

        if (!$this->footyStatsMatchXgView->exists($target)) {
            $creates[] = MatchXgView::getCreateSql($target);
        }

        if (!empty($creates)) {
            $this->io->section('Create');
            $this->io->writeln($creates);

            if (!$this->io->confirm('Proceed with the schema changes?')) {
                return Command::SUCCESS;
            }

            foreach ($creates as $create) {
                $this->footyStatsConnection->executeStatement($create);
            }
        }

        $teamNameIndex = [];
        foreach ($this->footyStatsScraper->scrapeTeamNames($target) as $teamNames) {
            $teamNameIndex[$teamNames[1]] = $teamNames[0];
        }

        $rawScrapedMatches = $this->footyStatsScraper->scrapeMatches($target);
        usort($rawScrapedMatches, fn(array $a, array $b) => $b['timestamp'] <=> $a['timestamp']);

        $found = [];
        $scrapedMatches = array_values(
            array_filter($rawScrapedMatches, function (array $match) use (&$found) {
                if (isset($found[$match['home_team_name']][$match['away_team_name']])) {
                    return false;
                }

                if (!isset($found[$match['home_team_name']])) {
                    $found[$match['home_team_name']] = [];
                }

                return $found[$match['home_team_name']][$match['away_team_name']] = true;
            })
        );

        $selectQueryBuilder = $this->footyStatsMatchTable
            ->createSelectQueryBuilder($target)
            ->select('*');

        $inserts = [];
        $updates = [];

        foreach ($scrapedMatches as $scrapedMatch) {
            $selectQueryBuilder->resetWhere();

            $homeTeamName = $teamNameIndex[$scrapedMatch['home_team_name']];
            $awayTeamName = $teamNameIndex[$scrapedMatch['away_team_name']];

            $dbMatch = $selectQueryBuilder
                ->where('home_team_name = :home_team_name')
                ->andWhere('away_team_name = :away_team_name')
                ->setParameter('home_team_name', $homeTeamName)
                ->setParameter('away_team_name', $awayTeamName)
                ->fetchAssociative();

            $candidate = [
                'home_team_name' => $homeTeamName,
                'away_team_name' => $awayTeamName
            ];

            if ($dbMatch === false) {
                $inserts[] = [...$scrapedMatch, ...$candidate];
                continue;
            }

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
            $this->io->definitionList(...$definitionList);
        }

        if (!$this->io->confirm('Proceed with the data changes?')) {
            return Command::SUCCESS;
        }

        $this->io->info('Backup table: ' . $this->footyStatsMatchTable->backup($target));

        $insertQueryBuilder = $this->footyStatsMatchTable
            ->createInsertQueryBuilder($target)
            ->values([
                'home_team_name' => '?',
                'away_team_name' => '?',
                'home_team_score' => '?',
                'away_team_score' => '?',
                'timestamp' => '?',
                'extra' => '?'
            ]);

        $this->footyStatsMatchTable->beginTransaction();

        try {
            $this->io->progressStart(count($inserts) + count($updates));

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
                $updateQueryBuilder = $this->footyStatsMatchTable
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
        } catch (Throwable $e) {
            $this->footyStatsMatchTable->rollback();
            throw $e;
        } finally {
            $this->io->progressFinish();
        }

        $this->footyStatsMatchTable->commit();

        $this->io->success(sprintf('Inserted %d rows. Updated %d rows', count($inserts), count($updates)));

        return Command::SUCCESS;
    }
}
