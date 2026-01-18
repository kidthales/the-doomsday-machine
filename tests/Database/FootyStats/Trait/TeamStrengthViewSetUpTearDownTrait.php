<?php

declare(strict_types=1);

namespace App\Tests\Database\FootyStats\Trait;

use App\Database\FootyStats\TeamStrengthView;
use Doctrine\DBAL\Exception as DBALException;
use Throwable;

trait TeamStrengthViewSetUpTearDownTrait
{
    /**
     * @return void
     * @throws DBALException
     */
    private function setUpTeamStrengthView(): void
    {
        try {
            $this->connection->executeStatement(TeamStrengthView::getDropSql($this->target));
        } catch (Throwable) {
        }

        $this->connection->executeStatement(TeamStrengthView::getCreateSql($this->target));
    }

    /**
     * @return void
     * @throws DBALException
     */
    private function tearDownTeamStrengthView(): void
    {
        $this->connection->executeStatement(TeamStrengthView::getDropSql($this->target));
    }
}
