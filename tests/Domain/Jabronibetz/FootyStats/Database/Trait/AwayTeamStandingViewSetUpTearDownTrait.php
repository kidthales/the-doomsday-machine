<?php

declare(strict_types=1);

namespace App\Tests\Domain\Jabronibetz\FootyStats\Database\Trait;

use App\Domain\Jabronibetz\FootyStats\Database\AwayTeamStandingView;
use Deprecated;
use Doctrine\DBAL\Exception as DBALException;
use Throwable;

#[Deprecated]
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
        } catch (Throwable) {
        }

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
