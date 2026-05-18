<?php

declare(strict_types=1);

namespace DoctrineMigrations\Jabronibetz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260518071713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename acronym column to/from short_name column in football_organization table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__football_organization AS
            SELECT
              id,
              name,
              acronym
            FROM
              football_organization
        SQL
        );
        $this->addSql('DROP TABLE football_organization');
        $this->addSql(<<<'SQL'
            CREATE TABLE football_organization (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              short_name VARCHAR(32) NOT NULL
            )
        SQL
        );
        $this->addSql(<<<'SQL'
            INSERT INTO football_organization (id, name, short_name)
            SELECT
              id,
              name,
              acronym
            FROM
              __temp__football_organization
        SQL
        );
        $this->addSql('DROP TABLE __temp__football_organization');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_NAME ON football_organization (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__football_organization AS
            SELECT
              id,
              name,
              short_name
            FROM
              football_organization
        SQL
        );
        $this->addSql('DROP TABLE football_organization');
        $this->addSql(<<<'SQL'
            CREATE TABLE football_organization (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              acronym VARCHAR(32) NOT NULL
            )
        SQL
        );
        $this->addSql(<<<'SQL'
            INSERT INTO football_organization (id, name, acronym)
            SELECT
              id,
              name,
              short_name
            FROM
              __temp__football_organization
        SQL
        );
        $this->addSql('DROP TABLE __temp__football_organization');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_NAME ON football_organization (name)');
    }
}
