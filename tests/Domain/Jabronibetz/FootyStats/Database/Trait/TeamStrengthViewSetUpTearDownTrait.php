<?php

declare(strict_types=1);

namespace App\Tests\Domain\Jabronibetz\FootyStats\Database\Trait;

use App\Domain\Jabronibetz\FootyStats\Database\TeamStrengthView;
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
