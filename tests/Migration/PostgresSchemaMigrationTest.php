<?php

declare(strict_types=1);

namespace App\Tests\Migration;

use App\Migration\AbstractPostgresSchemaMigration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\MigrationException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;

/**
 * @covers \App\Migration\AbstractPostgresSchemaMigration
 */
final class PostgresSchemaMigrationTest extends KernelTestCase
{
    public const string SCHEMA_NAME = 'test-schema';

    /**
     * @return void
     */
    public function test_getDescription(): void
    {
        self::bootKernel();

        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.migrator_connection');

        try {
            /** @var LoggerInterface $logger */
            $logger = self::getContainer()->get(LoggerInterface::class);

            $subject = self::getMigration($connection, $logger);

            self::assertSame('Create/Drop the `' . self::SCHEMA_NAME . '` schema', $subject->getDescription());
        } finally {
            $connection->close();
        }
    }

    /**
     * @return void
     * @throws Exception
     * @throws MigrationException
     */
    public function test_up(): void
    {
        self::bootKernel();

        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.migrator_connection');

        try {
            /** @var LoggerInterface $logger */
            $logger = self::getContainer()->get(LoggerInterface::class);

            $subject = self::getMigration($connection, $logger);

            $schema = self::createMock(Schema::class);

            $subject->up($schema);

            $queries = $subject->getSql();

            self::assertSame(13, count($queries));

            $q = array_shift($queries);

            self::assertSame('CREATE SCHEMA ' . self::SCHEMA_NAME, $q->getStatement());

            $reader = getenv('POSTGRES_READER_USER');

            $q = array_shift($queries);

            self::assertSame(
                'GRANT USAGE ON ALL SEQUENCES IN SCHEMA ' . self::SCHEMA_NAME . ' TO ' . $reader,
                $q->getStatement()
            );

            $q = array_shift($queries);

            self::assertSame(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA ' . self::SCHEMA_NAME . ' GRANT USAGE ON SEQUENCES TO ' . $reader,
                $q->getStatement()
            );

            $q = array_shift($queries);

            self::assertSame(
                'GRANT SELECT ON ALL TABLES IN SCHEMA ' . self::SCHEMA_NAME . ' TO ' . $reader,
                $q->getStatement()
            );

            $q = array_shift($queries);

            self::assertSame(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA ' . self::SCHEMA_NAME . ' GRANT SELECT ON TABLES TO ' . $reader,
                $q->getStatement()
            );

            $writer = getenv('POSTGRES_WRITER_USER');

            $q = array_shift($queries);

            self::assertSame(
                'GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA ' . self::SCHEMA_NAME . ' TO ' . $writer,
                $q->getStatement()
            );

            $q = array_shift($queries);

            self::assertSame(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA ' . self::SCHEMA_NAME . ' GRANT USAGE, SELECT ON SEQUENCES TO ' . $writer,
                $q->getStatement()
            );

            $q = array_shift($queries);

            self::assertSame(
                'GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA ' . self::SCHEMA_NAME . ' TO ' . $writer,
                $q->getStatement()
            );

            $q = array_shift($queries);

            self::assertSame(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA ' . self::SCHEMA_NAME . ' GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO ' . $writer,
                $q->getStatement()
            );

            $migrator = getenv('POSTGRES_MIGRATOR_USER');

            $q = array_shift($queries);

            self::assertSame(
                'GRANT ALL ON ALL SEQUENCES IN SCHEMA ' . self::SCHEMA_NAME . ' TO ' . $migrator,
                $q->getStatement()
            );

            $q = array_shift($queries);

            self::assertSame(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA ' . self::SCHEMA_NAME . ' GRANT ALL ON SEQUENCES TO ' . $migrator,
                $q->getStatement()
            );

            $q = array_shift($queries);

            self::assertSame(
                'GRANT SELECT, INSERT, UPDATE, DELETE, TRUNCATE ON ALL TABLES IN SCHEMA ' . self::SCHEMA_NAME . ' TO ' . $migrator,
                $q->getStatement()
            );

            $q = array_shift($queries);

            self::assertSame(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA ' . self::SCHEMA_NAME . ' GRANT SELECT, INSERT, UPDATE, DELETE, TRUNCATE ON TABLES TO ' . $migrator,
                $q->getStatement()
            );
        } finally {
            $connection->close();
        }
    }

    /**
     * @return void
     */
    public function test_down(): void
    {
        self::bootKernel();

        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.migrator_connection');

        try {
            /** @var LoggerInterface $logger */
            $logger = self::getContainer()->get(LoggerInterface::class);

            $subject = self::getMigration($connection, $logger);

            $schema = self::createMock(Schema::class);

            $subject->down($schema);

            $queries = $subject->getSql();

            self::assertSame(1, count($queries));

            $q = array_shift($queries);

            self::assertSame('DROP SCHEMA ' . self::SCHEMA_NAME, $q->getStatement());
        } finally {
            $connection->close();
        }
    }

    /**
     * @return void
     */
    public function test_envVariableNotFound(): void
    {
        self::bootKernel();

        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.migrator_connection');

        $reader = getenv('POSTGRES_READER_USER');

        try {
            /** @var LoggerInterface $logger */
            $logger = self::getContainer()->get(LoggerInterface::class);

            $subject = self::getMigration($connection, $logger);

            $schema = self::createMock(Schema::class);

            putenv('POSTGRES_READER_USER=');
            putenv('POSTGRES_READER_USER');

            $subject->up($schema);

            self::fail('Expected exception not thrown');
        } catch (Throwable $e) {
            self::assertInstanceOf(RuntimeException::class, $e);
            self::assertSame('POSTGRES_READER_USER env variable not found', $e->getMessage());
        } finally {
            $connection->close();
            putenv(sprintf('POSTGRES_READER_USER=%s', $reader));
        }
    }

    /**
     * @param Connection $connection
     * @param LoggerInterface $logger
     * @return AbstractPostgresSchemaMigration
     */
    private static function getMigration(Connection $connection, LoggerInterface $logger): AbstractPostgresSchemaMigration
    {
        return new class($connection, $logger) extends AbstractPostgresSchemaMigration {
            protected const string SCHEMA_NAME = PostgresSchemaMigrationTest::SCHEMA_NAME;
        };
    }
}
