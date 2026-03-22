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

use App\Console\Command\FootyStats\AbstractTargetCommand as Command;
use App\Database\FootyStats\AwayTeamStandingView;
use App\Database\FootyStats\AwayTeamStandingViewAwareTrait;
use App\Database\FootyStats\ConnectionAwareTrait;
use App\Database\FootyStats\DeductionTable;
use App\Database\FootyStats\DeductionTableAwareTrait;
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
use App\Entity\FootyStats\Target;
use App\Provider\FootyStats\TargetArgumentsProviderInterface;
use App\Scraper\FootyStatsScraperAwareTrait;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[AsCommand(
    name: 'app:footy-stats:database:diff',
    description: 'Insert or update Footy Stats table data'
)]
final class DiffCommand extends Command
{
    use AwayTeamStandingViewAwareTrait,
        ConnectionAwareTrait,
        DeductionTableAwareTrait,
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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Throwable
     * @throws DBALException
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Diff Footy Stats Database');

        $target = $this->getTargetArguments($input);
        $this->io->info((string)$target);

        $createTableSql = $this->getCreateTableSql($target);
        $createViewSql = $this->getCreateViewSql($target);
        $createSql = [...$createTableSql, ...$createViewSql];

        if (!empty($createSql)) {
            $this->io->section('Create');
            $this->io->writeln($createSql);

            if (!$this->io->confirm('Proceed with the schema changes?')) {
                return Command::SUCCESS;
            }

            foreach ($createSql as $sql) {
                $this->footyStatsConnection->executeStatement($sql);
            }
        }

        $teamNameIndex = $this->getTeamNameIndex($target);
        $deductionTableInsertCount = $this->insertTeamsIntoDeductionTable($target, $teamNameIndex);
        list ($inserts, $updates) = $this->getMatchTableChanges($target, $teamNameIndex);

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

        $this->doMatchTableChanges($target, $inserts, $updates);

        $this->io->success(
            sprintf(
                'Created %d tables. Created %d views. Inserted %d rows. Updated %d rows',
                count($createTableSql),
                count($createViewSql),
                count($inserts) + $deductionTableInsertCount,
                count($updates)
            )
        );

        return Command::SUCCESS;
    }

    /**
     * @param Target $target
     * @return array
     * @throws DBALException
     */
    private function getCreateTableSql(Target $target): array
    {
        $sql = [];

        if (!$this->footyStatsMatchTable->exists($target)) {
            $sql[] = MatchTable::getCreateSql($target);
        }

        if (!$this->footyStatsDeductionTable->exists($target)) {
            $sql[] = DeductionTable::getCreateSql($target);
        }

        return $sql;
    }

    /**
     * @param Target $target
     * @return array
     * @throws DBALException
     */
    private function getCreateViewSql(Target $target): array
    {
        $sql = [];

        if (!$this->footyStatsTeamStandingView->exists($target)) {
            $sql[] = TeamStandingView::getCreateSql($target);
        }

        if (!$this->footyStatsHomeTeamStandingView->exists($target)) {
            $sql[] = HomeTeamStandingView::getCreateSql($target);
        }

        if (!$this->footyStatsAwayTeamStandingView->exists($target)) {
            $sql[] = AwayTeamStandingView::getCreateSql($target);
        }

        if (!$this->footyStatsTeamStrengthView->exists($target)) {
            $sql[] = TeamStrengthView::getCreateSql($target);
        }

        if (!$this->footyStatsMatchXgView->exists($target)) {
            $sql[] = MatchXgView::getCreateSql($target);
        }

        return $sql;
    }

    /**
     * @param Target $target
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getTeamNameIndex(Target $target): array
    {
        $teamNameIndex = [];

        foreach ($this->footyStatsScraper->scrapeTeamNames($target) as $teamNames) {
            $teamNameIndex[$teamNames[1]] = $teamNames[0];
        }

        return $teamNameIndex;
    }

    /**
     * @param Target $target
     * @param string[] $teamNames
     * @return int
     * @throws DBALException
     */
    private function insertTeamsIntoDeductionTable(Target $target, array $teamNames): int
    {
        $selectQueryBuilder = $this->footyStatsDeductionTable
            ->createSelectQueryBuilder($target)
            ->select('1');

        $insertQueryBuilder = $this->footyStatsDeductionTable
            ->createInsertQueryBuilder($target)
            ->values([
                'team_name' => '?',
                'points' => '?',
                'extra' => '?'
            ]);

        $count = 0;

        foreach ($teamNames as $teamName) {
            $selectQueryBuilder
                ->resetWhere()
                ->where('team_name = :team_name')
                ->setParameter('team_name', $teamName);

            if (!$selectQueryBuilder->fetchOne()) {
                ++$count;
                $insertQueryBuilder
                    ->setParameters([$teamName, 0, null])
                    ->executeStatement();
            }
        }

        return $count;
    }

    /**
     * @param Target $target
     * @param array $teamNameIndex
     * @return array[]
     * @throws ClientExceptionInterface
     * @throws DBALException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getMatchTableChanges(Target $target, array $teamNameIndex): array
    {
        $scrapedMatches = $this->getScrapedMatches($target);

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

        return ['inserts' => $inserts, 'updates' => $updates];
    }

    /**
     * @param Target $target
     * @param array $inserts
     * @param array $updates
     * @return void
     * @throws DBALException
     * @throws Throwable
     */
    private function doMatchTableChanges(Target $target, array $inserts, array $updates): void
    {
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
    }

    /**
     * @param Target $target
     * @return array
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getScrapedMatches(Target $target): array
    {
        // Sort by timestamp, descending. Then filter out duplicates, first found, first kept.
        // This should ensure we only get the latest matches in cases of match postponement

        $rawScrapedMatches = $this->footyStatsScraper->scrapeMatches($target);
        usort($rawScrapedMatches, fn(array $a, array $b) => $b['timestamp'] <=> $a['timestamp']);

        $found = [];
        return array_values(
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
    }
}
