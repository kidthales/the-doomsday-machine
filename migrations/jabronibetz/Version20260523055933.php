<?php

declare(strict_types=1);

namespace DoctrineMigrations\Jabronibetz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260523055933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add/remove gender column in football_team table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__football_team AS
            SELECT
              id,
              managing_organization_id,
              name,
              short_name
            FROM
              football_team
        SQL);
        $this->addSql('DROP TABLE football_team');
        $this->addSql(<<<'SQL'
            CREATE TABLE football_team (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              managing_organization_id INTEGER DEFAULT NULL,
              name VARCHAR(255) NOT NULL,
              short_name VARCHAR(32) NOT NULL,
              gender VARCHAR(8) NOT NULL,
              CONSTRAINT FK_C53936CADD9F7FF2 FOREIGN KEY (managing_organization_id) REFERENCES football_organization (id) ON
              UPDATE
                NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO football_team (
              id, managing_organization_id, name,
              short_name, gender
            )
            SELECT
              id,
              managing_organization_id,
              name,
              short_name,
              'male' AS gender
            FROM
              __temp__football_team
        SQL);
        $this->addSql('DROP TABLE __temp__football_team');
        $this->addSql('CREATE INDEX IDX_C53936CADD9F7FF2 ON football_team (managing_organization_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_FOOTBALL_TEAM_NAME_GENDER ON football_team (name, gender)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__football_team AS
            SELECT
              id,
              managing_organization_id,
              name,
              short_name
            FROM
              football_team
        SQL);
        $this->addSql('DROP TABLE football_team');
        $this->addSql(<<<'SQL'
            CREATE TABLE football_team (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              managing_organization_id INTEGER DEFAULT NULL,
              name VARCHAR(255) NOT NULL,
              short_name VARCHAR(32) NOT NULL,
              CONSTRAINT FK_C53936CADD9F7FF2 FOREIGN KEY (managing_organization_id) REFERENCES football_organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO football_team (
              id, managing_organization_id, name,
              short_name
            )
            SELECT
              id,
              managing_organization_id,
              name,
              short_name
            FROM
              __temp__football_team
        SQL);
        $this->addSql('DROP TABLE __temp__football_team');
        $this->addSql('CREATE INDEX IDX_C53936CADD9F7FF2 ON football_team (managing_organization_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_FOOTBALL_TEAM_NAME ON football_team (name)');
    }
}
