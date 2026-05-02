<?php

declare(strict_types=1);

namespace App\Tests\Domain\Jabronibetz\FootyStats\Database\Trait;

use App\Domain\Jabronibetz\FootyStats\Database\HomeTeamStandingView;
use Doctrine\DBAL\Exception as DBALException;
use Throwable;

trait HomeTeamStandingViewSetUpTearDownTrait
{
    /**
     * @return void
     * @throws DBALException
     */
    private function setUpHomeTeamStandingView(): void
    {
        try {
            $this->connection->executeStatement(HomeTeamStandingView::getDropSql($this->target));
        } catch (Throwable) {
        }

        $this->connection->executeStatement(HomeTeamStandingView::getCreateSql($this->target));
    }

    /**
     * @return void
     * @throws DBALException
     */
    private function tearDownHomeTeamStandingView(): void
    {
        $this->connection->executeStatement(HomeTeamStandingView::getDropSql($this->target));
    }
}
