<?php

namespace App\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use RuntimeException;

abstract class AbstractPostgresSchemaMigration extends AbstractMigration
{
    /**
     * Set in child classes.
     */
    protected const string SCHEMA_NAME = '';

    private const string POSTGRES_READER_USER = 'POSTGRES_READER_USER';
    private const string POSTGRES_WRITER_USER = 'POSTGRES_WRITER_USER';
    private const string POSTGRES_MIGRATOR_USER = 'POSTGRES_MIGRATOR_USER';

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return sprintf('Create/Drop the `%s` schema', static::SCHEMA_NAME);
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema): void
    {
        $this->addSql(sprintf('CREATE SCHEMA %s', static::SCHEMA_NAME));
        $this->grantReaderUser();
        $this->grantWriterUser();
        $this->grantMigratorUser();
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->addSql(sprintf('DROP SCHEMA %s', static::SCHEMA_NAME));
    }

    /**
     * @return void
     */
    private function grantReaderUser(): void
    {
        $user = $this->getReaderUser();

        // Existing sequences
        $this->addSql(
            sprintf(
                'GRANT USAGE ON ALL SEQUENCES IN SCHEMA %s TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );

        // Future sequences
        $this->addSql(
            sprintf(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA %s GRANT USAGE ON SEQUENCES TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );

        // Existing tables
        $this->addSql(
            sprintf(
                'GRANT SELECT ON ALL TABLES IN SCHEMA %s TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );

        // Future tables
        $this->addSql(
            sprintf(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA %s GRANT SELECT ON TABLES TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );
    }

    /**
     * @return void
     */
    private function grantWriterUser(): void
    {
        $user = $this->getWriterUser();

        // Existing sequences
        $this->addSql(
            sprintf(
                'GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA %s TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );

        // Future sequences
        $this->addSql(
            sprintf(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA %s GRANT USAGE, SELECT ON SEQUENCES TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );

        // Existing tables
        $this->addSql(
            sprintf(
                'GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA %s TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );

        // Future tables
        $this->addSql(
            sprintf(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA %s GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );
    }

    /**
     * @return void
     */
    private function grantMigratorUser(): void
    {
        $user = $this->getMigratorUser();

        // Existing sequences
        $this->addSql(
            sprintf(
                'GRANT ALL ON ALL SEQUENCES IN SCHEMA %s TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );

        // Future sequences
        $this->addSql(
            sprintf(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA %s GRANT ALL ON SEQUENCES TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );

        // Existing tables
        $this->addSql(
            sprintf(
                'GRANT SELECT, INSERT, UPDATE, DELETE, TRUNCATE ON ALL TABLES IN SCHEMA %s TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );

        // Future tables
        $this->addSql(
            sprintf(
                'ALTER DEFAULT PRIVILEGES IN SCHEMA %s GRANT SELECT, INSERT, UPDATE, DELETE, TRUNCATE ON TABLES TO %s',
                static::SCHEMA_NAME,
                $user
            )
        );
    }

    /**
     * @return string
     */
    private function getReaderUser(): string
    {
        return $this->getUser(self::POSTGRES_READER_USER);
    }

    /**
     * @return string
     */
    private function getWriterUser(): string
    {
        return $this->getUser(self::POSTGRES_WRITER_USER);
    }

    /**
     * @return string
     */
    private function getMigratorUser(): string
    {
        return $this->getUser(self::POSTGRES_MIGRATOR_USER);
    }

    /**
     * @param string $envVariableName
     * @return string
     */
    private function getUser(string $envVariableName): string
    {
        $user = getenv($envVariableName);

        if (!$user) {
            throw new RuntimeException(sprintf('%s env variable not found', $envVariableName));
        }

        return $user;
    }
}
