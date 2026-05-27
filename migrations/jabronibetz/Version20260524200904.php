<?php

declare(strict_types=1);

namespace DoctrineMigrations\Jabronibetz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524200904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the football_competition_team_entry table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE football_competition_team_entry (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              competition_id INTEGER DEFAULT NULL,
              team_id INTEGER DEFAULT NULL,
              "group" VARCHAR(1) NOT NULL,
              result VARCHAR(128) NOT NULL,
              CONSTRAINT FK_CC763D257B39D312 FOREIGN KEY (competition_id) REFERENCES football_competition (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_CC763D25296CD8AE FOREIGN KEY (team_id) REFERENCES football_team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL
        );
        $this->addSql('CREATE INDEX IDX_CC763D257B39D312 ON football_competition_team_entry (competition_id)');
        $this->addSql('CREATE INDEX IDX_CC763D25296CD8AE ON football_competition_team_entry (team_id)');
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_FOOTBALL_COMPETITION_TEAM_ENTRY_COMPETITION_ID_TEAM_ID ON football_competition_team_entry (competition_id, team_id)
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE football_competition_team_entry');
    }
}
