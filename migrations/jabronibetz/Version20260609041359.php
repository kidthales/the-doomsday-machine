<?php

declare(strict_types=1);

namespace DoctrineMigrations\Jabronibetz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260609041359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the view_football_match_team_reference_frame view.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE VIEW view_football_match_team_reference_frame AS
            SELECT ROW_NUMBER() OVER (ORDER BY 1) AS id,
                   *
            FROM(SELECT id                             AS match_id,
                        competition_id,
                        home_team_id                   AS team_id,
                        timestamp,
                        round,
                        home_team_halftime_score       AS halftime_goals_for,
                        away_team_halftime_score       AS halftime_goals_against,
                        home_team_fulltime_score       AS fulltime_goals_for,
                        away_team_fulltime_score       AS fulltime_goals_against,
                        home_team_extra_halftime_score AS extra_halftime_goals_for,
                        away_team_extra_halftime_score AS extra_halftime_goals_against,
                        home_team_extra_fulltime_score AS extra_fulltime_goals_for,
                        away_team_extra_fulltime_score AS extra_fulltime_goals_against,
                        home_team_shootout_score       AS shootout_goals_for,
                        away_team_shootout_score       AS shootout_goals_against,
                        TRUE                           AS home_team,
                        FALSE                          AS away_team
                 FROM football_match
                 UNION ALL
                 SELECT id                             AS match_id,
                        competition_id,
                        away_team_id                   AS team_id,
                        timestamp,
                        round,
                        away_team_halftime_score       AS halftime_goals_for,
                        home_team_halftime_score       AS halftime_goals_against,
                        away_team_fulltime_score       AS fulltime_goals_for,
                        home_team_fulltime_score       AS fulltime_goals_against,
                        away_team_extra_halftime_score AS extra_halftime_goals_for,
                        home_team_extra_halftime_score AS extra_halftime_goals_against,
                        away_team_extra_fulltime_score AS extra_fulltime_goals_for,
                        home_team_extra_fulltime_score AS extra_fulltime_goals_against,
                        away_team_shootout_score       AS shootout_goals_for,
                        home_team_shootout_score       AS shootout_goals_against,
                        FALSE                          AS home_team,
                        TRUE                           AS away_team
                 FROM football_match)
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW view_football_match_team_reference_frame');
    }
}
