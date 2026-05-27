<?php

declare(strict_types=1);

namespace DoctrineMigrations\Jabronibetz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260526184636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the football_match table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE football_match (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              competition_id INTEGER DEFAULT NULL,
              home_team_id INTEGER DEFAULT NULL,
              away_team_id INTEGER DEFAULT NULL,
              timestamp BIGINT DEFAULT NULL,
              round SMALLINT DEFAULT NULL,
              home_team_halftime_score SMALLINT DEFAULT NULL,
              away_team_halftime_score SMALLINT DEFAULT NULL,
              home_team_fulltime_score SMALLINT DEFAULT NULL,
              away_team_fulltime_score SMALLINT DEFAULT NULL,
              home_team_extra_halftime_score SMALLINT DEFAULT NULL,
              away_team_extra_halftime_score SMALLINT DEFAULT NULL,
              home_team_extra_fulltime_score SMALLINT DEFAULT NULL,
              away_team_extra_fulltime_score SMALLINT DEFAULT NULL,
              home_team_shootout_score SMALLINT DEFAULT NULL,
              away_team_shootout_score SMALLINT DEFAULT NULL,
              CONSTRAINT FK_8CE33ACE7B39D312 FOREIGN KEY (competition_id) REFERENCES football_competition (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_8CE33ACE9C4C13F6 FOREIGN KEY (home_team_id) REFERENCES football_team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_8CE33ACE45185D02 FOREIGN KEY (away_team_id) REFERENCES football_team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL
        );
        $this->addSql('CREATE INDEX IDX_8CE33ACE7B39D312 ON football_match (competition_id)');
        $this->addSql('CREATE INDEX IDX_8CE33ACE9C4C13F6 ON football_match (home_team_id)');
        $this->addSql('CREATE INDEX IDX_8CE33ACE45185D02 ON football_match (away_team_id)');
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_FOOTBALL_MATCH_COMPETITION_ID_HOME_TEAM_ID_AWAY_TEAM_ID_ROUND ON football_match (
              competition_id, home_team_id, away_team_id,
              round
            )
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE football_match');
    }
}
