<?php

declare(strict_types=1);

namespace DoctrineMigrations\Jabronibetz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522040254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the football_team table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE football_team (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              managing_organization_id INTEGER DEFAULT NULL,
              name VARCHAR(255) NOT NULL,
              short_name VARCHAR(32) NOT NULL,
              CONSTRAINT FK_C53936CADD9F7FF2 FOREIGN KEY (managing_organization_id) REFERENCES football_organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_C53936CADD9F7FF2 ON football_team (managing_organization_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_FOOTBALL_TEAM_NAME ON football_team (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE football_team');
    }
}
