<?php

declare(strict_types=1);

namespace App\Tests\FootyStats\Database\Trait;

use App\FootyStats\Database\AwayTeamStandingView;
use Doctrine\DBAL\Exception as DBALException;
use Throwable;

trait AwayTeamStandingViewSetUpTearDownTrait
{
    /**
     * @return void
     * @throws DBALException
     */
    private function setUpAwayTeamStandingView(): void
    {
        try {
            $this->connection->executeStatement(AwayTeamStandingView::getDropSql($this->target));
        } catch (Throwable) {}

        $this->connection->executeStatement(AwayTeamStandingView::getCreateSql($this->target));
    }

    /**
     * @return void
     * @throws DBALException
     */
    private function tearDownAwayTeamStandingView(): void
    {
        $this->connection->executeStatement(AwayTeamStandingView::getDropSql($this->target));
    }
}
