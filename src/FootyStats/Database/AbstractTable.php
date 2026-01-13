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
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use function Symfony\Component\String\s;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
abstract readonly class AbstractTable extends AbstractTableOrView
{
    public static function getDropSql(Target $target): string
    {
        $sql = <<<'SQL'
            DROP TABLE <table_name>;
SQL;

        return s($sql)->replace('<table_name>', static::getName($target))->toString();
    }

    /**
     * @param Target $target
     * @return bool
     * @throws DBALException
     */
    public function exists(Target $target): bool
    {
        return $this->checkTableOrView('table', $target);
    }

    public function createInsertQueryBuilder(Target $target): QueryBuilder
    {
        return $this->connection->createQueryBuilder()->insert(static::getName($target));
    }

    public function createUpdateQueryBuilder(Target $target, ?string $alias = null): QueryBuilder
    {
        return $this->connection->createQueryBuilder()->update(static::getName($target), $alias);
    }

    public function createDeleteQueryBuilder(Target $target, ?string $alias = null): QueryBuilder
    {
        return $this->connection->createQueryBuilder()->delete(static::getName($target), $alias);
    }
}
