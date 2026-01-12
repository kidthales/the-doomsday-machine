<?php

declare(strict_types=1);

namespace App\Tests\FootyStats\Database\Trait;

use App\FootyStats\Database\MatchTable;
use Doctrine\DBAL\Exception as DBALException;
use Throwable;

trait MatchTableSetUpTearDownTrait
{
    /**
     * @return void
     * @throws DBALException
     */
    private function setUpMatchTable(): void
    {
        try {
            $this->connection->executeStatement(MatchTable::getDropSql($this->target));
        } catch (Throwable) {}

        $this->connection->executeStatement(MatchTable::getCreateSql($this->target));
    }

    /**
     * @return void
     * @throws DBALException
     */
    private function tearDownMatchTable(): void
    {
        $this->connection->executeStatement(MatchTable::getDropSql($this->target));
    }
}
