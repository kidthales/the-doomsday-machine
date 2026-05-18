<?php

declare(strict_types=1);

namespace App\Tests\Domain\Jabronibetz\FootyStats\Database\Trait;

use App\Domain\Jabronibetz\FootyStats\Database\TeamStandingView;
use Deprecated;
use Doctrine\DBAL\Exception as DBALException;
use Throwable;

#[Deprecated]
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
        } catch (Throwable) {
        }

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
