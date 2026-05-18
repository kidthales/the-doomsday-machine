<?php

declare(strict_types=1);

namespace DoctrineMigrations\Jabronibetz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260518212053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return <<<'TXT'
        Create/drop the football_competition table; rename UNIQ_IDENTIFIER_NAME index
        to/from UNIQ_IDENTIFIER_FOOTBALL_ORGANIZATION_NAME in football_organization
        table.
        TXT;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE football_competition (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              organization_id INTEGER DEFAULT NULL,
              name VARCHAR(255) NOT NULL,
              short_name VARCHAR(32) NOT NULL,
              CONSTRAINT FK_6DCE6C5D32C8A3DE FOREIGN KEY (organization_id) REFERENCES football_organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_6DCE6C5D32C8A3DE ON football_competition (organization_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_FOOTBALL_COMPETITION_NAME ON football_competition (name)');
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__football_organization AS
            SELECT
              id,
              name,
              short_name
            FROM
              football_organization
        SQL);
        $this->addSql('DROP TABLE football_organization');
        $this->addSql(<<<'SQL'
            CREATE TABLE football_organization (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              short_name VARCHAR(32) NOT NULL
            )
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO football_organization (id, name, short_name)
            SELECT
              id,
              name,
              short_name
            FROM
              __temp__football_organization
        SQL);
        $this->addSql('DROP TABLE __temp__football_organization');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_FOOTBALL_ORGANIZATION_NAME ON football_organization (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE football_competition');
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__football_organization AS
            SELECT
              id,
              name,
              short_name
            FROM
              football_organization
        SQL);
        $this->addSql('DROP TABLE football_organization');
        $this->addSql(<<<'SQL'
            CREATE TABLE football_organization (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              short_name VARCHAR(32) NOT NULL
            )
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO football_organization (id, name, short_name)
            SELECT
              id,
              name,
              short_name
            FROM
              __temp__football_organization
        SQL);
        $this->addSql('DROP TABLE __temp__football_organization');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_NAME ON football_organization (name)');
    }
}
