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

namespace App\FootyStats\Database;

use App\FootyStats\Target;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use LogicException;
use function Symfony\Component\String\s;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
abstract readonly class AbstractTableOrView
{
    /**
     * Define in concrete child class implementations.
     */
    public const ?string BASE_NAME = null;

    /**
     * Get the table or view name for the specified target.
     *
     * @param Target $target
     * @return string
     */
    final public static function getName(Target $target): string
    {
        // @codeCoverageIgnoreStart
        if (static::BASE_NAME === null) {
            throw new LogicException('BASE_NAME is not defined');
        }
        // @codeCoverageIgnoreEnd

        return sprintf('footy_stats_%s_%s', $target->snake(), s(static::BASE_NAME)->snake()->toString());
    }

    abstract public static function getCreateSql(Target $target): string;

    abstract public static function getDropSql(Target $target): string;

    public function __construct(protected Connection $connection)
    {
    }

    abstract public function exists(Target $target): bool;

    public function createSelectQueryBuilder(Target $target, ?string $alias = null): QueryBuilder
    {
        return $this->connection->createQueryBuilder()->from(static::getName($target), $alias);
    }

    /**
     * @param string $type
     * @param Target $target
     * @return bool
     * @throws DBALException
     */
    final protected function checkTableOrView(string $type, Target $target): bool
    {
        return (bool)$this->connection
            ->executeQuery('SELECT COUNT(*) FROM sqlite_master WHERE type = ? AND name = ?', [$type, static::getName($target)])
            ->fetchOne();
    }
}
