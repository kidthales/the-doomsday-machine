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

namespace App\Provider\FootyStats;

use App\Entity\FootyStats\Target;
use App\Scraper\FootyStatsScraperAwareTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use function Symfony\Component\String\s;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class DatabaseTargetArgumentsProvider implements TargetArgumentsProviderInterface
{
    use FootyStatsScraperAwareTrait;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array
     * @throws DBALException
     */
    public function getNations(): array
    {
        $target = new Target();

        $selectQueryBuilder = $this->connection
            ->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('sqlite_master');

        $nations = [];

        foreach ($this->footyStatsScraper->getNations() as $nation) {
            $target->nation = $nation;

            $count = $selectQueryBuilder
                ->where('name LIKE :name')
                ->setParameter('name', 'footy_stats_' . $target->snake() . '%_match')
                ->andWhere("type = 'table'")
                ->fetchOne();

            if ($count !== 0) {
                $nations[] = $nation;
            }

            $selectQueryBuilder->resetWhere();
        }

        return $nations;
    }

    /**
     * @param string $nation
     * @return array
     * @throws DBALException
     */
    public function getCompetitions(string $nation): array
    {
        $target = new Target($nation);

        $selectQueryBuilder = $this->connection
            ->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('sqlite_master');

        $competitions = [];

        foreach ($this->footyStatsScraper->getCompetitions($nation) as $competition) {
            $target->competition = $competition;

            $count = $selectQueryBuilder
                ->where('name LIKE :name')
                ->setParameter('name', 'footy_stats_' . $target->snake() . '%_match')
                ->andWhere("type = 'table'")
                ->fetchOne();

            if ($count !== 0) {
                $competitions[] = $competition;
            }

            $selectQueryBuilder->resetWhere();
        }

        return $competitions;
    }

    /**
     * @param string $nation
     * @param string $competition
     * @return array
     * @throws DBALException
     */
    public function getSeasons(string $nation, string $competition): array
    {
        $target = new Target($nation, $competition);

        $tableNames = $this->connection
            ->createQueryBuilder()
            ->select('name')
            ->from('sqlite_master')
            ->where('name LIKE :name')
            ->setParameter('name', 'footy_stats_' . $target->snake() . '%_match')
            ->andWhere("type = 'table'")
            ->fetchFirstColumn();

        $seasons = [];

        foreach ($tableNames as $tableName) {
            $season = s($tableName)
                ->replace('footy_stats_' . $target->snake(), '')
                ->replace('match', '')
                ->trim('_')
                ->toString();

            if (preg_match('/^\d{6}$/', $season) === 1) {
                $season = substr_replace($season, '/', 4, 0);
            }

            $seasons[] = $season;
        }

        return $seasons;
    }
}
