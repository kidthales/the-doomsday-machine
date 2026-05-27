<?php

declare(strict_types=1);

namespace DoctrineMigrations\Jabronibetz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524215232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Alter nullability of group and result columns in football competition team entry table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__football_competition_team_entry AS
            SELECT
              id,
              competition_id,
              team_id,
              "group",
              result
            FROM
              football_competition_team_entry
        SQL
        );
        $this->addSql('DROP TABLE football_competition_team_entry');
        $this->addSql(<<<'SQL'
            CREATE TABLE football_competition_team_entry (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              competition_id INTEGER DEFAULT NULL,
              team_id INTEGER DEFAULT NULL,
              "group" VARCHAR(1) DEFAULT NULL,
              result VARCHAR(128) DEFAULT NULL,
              CONSTRAINT FK_CC763D257B39D312 FOREIGN KEY (competition_id) REFERENCES football_competition (id) ON
              UPDATE
                NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
                CONSTRAINT FK_CC763D25296CD8AE FOREIGN KEY (team_id) REFERENCES football_team (id) ON
              UPDATE
                NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL
        );
        $this->addSql(<<<'SQL'
            INSERT INTO football_competition_team_entry (
              id, competition_id, team_id, "group",
              result
            )
            SELECT
              id,
              competition_id,
              team_id,
              "group",
              result
            FROM
              __temp__football_competition_team_entry
        SQL
        );
        $this->addSql('DROP TABLE __temp__football_competition_team_entry');
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_FOOTBALL_COMPETITION_TEAM_ENTRY_COMPETITION_ID_TEAM_ID ON football_competition_team_entry (competition_id, team_id)
        SQL
        );
        $this->addSql('CREATE INDEX IDX_CC763D25296CD8AE ON football_competition_team_entry (team_id)');
        $this->addSql('CREATE INDEX IDX_CC763D257B39D312 ON football_competition_team_entry (competition_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__football_competition_team_entry AS
            SELECT
              id,
              competition_id,
              team_id,
              "group",
              result
            FROM
              football_competition_team_entry
        SQL
        );
        $this->addSql('DROP TABLE football_competition_team_entry');
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
        $this->addSql(<<<'SQL'
            INSERT INTO football_competition_team_entry (
              id, competition_id, team_id, "group",
              result
            )
            SELECT
              id,
              competition_id,
              team_id,
              "group",
              result
            FROM
              __temp__football_competition_team_entry
        SQL
        );
        $this->addSql('DROP TABLE __temp__football_competition_team_entry');
        $this->addSql('CREATE INDEX IDX_CC763D257B39D312 ON football_competition_team_entry (competition_id)');
        $this->addSql('CREATE INDEX IDX_CC763D25296CD8AE ON football_competition_team_entry (team_id)');
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_FOOTBALL_COMPETITION_TEAM_ENTRY_COMPETITION_ID_TEAM_ID ON football_competition_team_entry (competition_id, team_id)
        SQL
        );
    }
}
