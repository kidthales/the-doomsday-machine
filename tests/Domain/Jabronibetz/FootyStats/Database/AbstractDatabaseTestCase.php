<?php

declare(strict_types=1);

namespace App\Tests\Domain\Jabronibetz\FootyStats\Database;

use App\Domain\Jabronibetz\FootyStats\Target\Target;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractDatabaseTestCase extends KernelTestCase
{
    protected ?Connection $connection = null;
    protected ?Target $target = null;

    public function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->connection = $kernel->getContainer()->get('doctrine.dbal.jabronibetz_footy_stats_connection');
        $this->target = new Target('Test', 'Test', 'Test');
    }

    public function tearDown(): void
    {
        $this->connection->close();

        $this->connection = null;
        $this->target = null;

        parent::tearDown();
    }
}
