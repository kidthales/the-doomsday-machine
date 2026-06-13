<?php

declare(strict_types=1);

namespace DoctrineMigrations\Jabronibetz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260610035533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add/remove the rounds column and the group_rounds columns to/from the football_match table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE football_competition ADD COLUMN rounds SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE football_competition ADD COLUMN group_rounds SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__football_competition AS
            SELECT
              id,
              managing_organization_id,
              name,
              short_name
            FROM
              football_competition
        SQL
        );
        $this->addSql('DROP TABLE football_competition');
        $this->addSql(<<<'SQL'
            CREATE TABLE football_competition (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              managing_organization_id INTEGER DEFAULT NULL,
              name VARCHAR(255) NOT NULL,
              short_name VARCHAR(32) NOT NULL,
              CONSTRAINT FK_6DCE6C5DDD9F7FF2 FOREIGN KEY (managing_organization_id) REFERENCES football_organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL
        );
        $this->addSql(<<<'SQL'
            INSERT INTO football_competition (
              id, managing_organization_id, name,
              short_name
            )
            SELECT
              id,
              managing_organization_id,
              name,
              short_name
            FROM
              __temp__football_competition
        SQL
        );
        $this->addSql('DROP TABLE __temp__football_competition');
        $this->addSql('CREATE INDEX IDX_6DCE6C5DDD9F7FF2 ON football_competition (managing_organization_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_FOOTBALL_COMPETITION_NAME ON football_competition (name)');
    }
}
