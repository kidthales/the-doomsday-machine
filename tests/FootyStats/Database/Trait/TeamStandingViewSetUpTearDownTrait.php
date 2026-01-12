<?php

declare(strict_types=1);

namespace App\Tests\FootyStats\Database\Trait;

use App\FootyStats\Database\TeamStandingView;
use Doctrine\DBAL\Exception as DBALException;
use Throwable;

trait TeamStandingViewSetUpTearDownTrait
{
    /**
     * @return void
     * @throws DBALException
     */
    private function setUpTeamStandingView(): void
    {
        try {
            $this->connection->executeStatement(TeamStandingView::getDropSql($this->target));
        } catch (Throwable) {}

        $this->connection->executeStatement(TeamStandingView::getCreateSql($this->target));
    }

    /**
     * @return void
     * @throws DBALException
     */
    private function tearDownTeamStandingView(): void
    {
        $this->connection->executeStatement(TeamStandingView::getDropSql($this->target));
    }
}
