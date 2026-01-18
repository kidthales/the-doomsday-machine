<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260118002706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Footy Stats: Germany Bundesliga 2007/08';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE footy_stats_germany_bundesliga_200708_match (
                home_team_name TEXT NOT NULL,
                away_team_name TEXT NOT NULL,
                home_team_score SMALLINT DEFAULT NULL,
                away_team_score SMALLINT DEFAULT NULL,
                timestamp BIGINT DEFAULT NULL,
                extra TEXT DEFAULT NULL,
                PRIMARY KEY (home_team_name, away_team_name)
            );
        SQL
        );

        $this->addSql(<<<'SQL'
            CREATE VIEW footy_stats_germany_bundesliga_200708_team_standing AS
            SELECT ROW_NUMBER() OVER (ORDER BY 1) AS position, *
            FROM(SELECT team_name,
                        COUNT(team_name)                                                                                 AS matches_played,
                        SUM(CASE WHEN goals_for > goals_against THEN 1 ELSE 0 END)                                       AS wins,
                        SUM(CASE WHEN goals_for = goals_against THEN 1 ELSE 0 END)                                       AS draws,
                        SUM(CASE WHEN goals_for < goals_against THEN 1 ELSE 0 END)                                       AS losses,
                        SUM(goals_for)                                                                                   AS goals_for,
                        SUM(goals_for) / CAST(COUNT(team_name) AS FLOAT)                                                 AS goals_for_per_game,
                        SUM(goals_against)                                                                               AS goals_against,
                        SUM(goals_against) / CAST(COUNT(team_name) AS FLOAT)                                             AS goals_against_per_game,
                        SUM(goals_for) - SUM(goals_against)                                                              AS goal_difference,
                        SUM(CASE WHEN goals_for > goals_against THEN 3 WHEN goals_for = goals_against THEN 1 ELSE 0 END) AS points,
                        SUM(CASE WHEN goals_for > goals_against THEN 3 WHEN goals_for = goals_against THEN 1 ELSE 0 END) /
                        CAST(COUNT(team_name) AS FLOAT)                                                                  AS points_per_game,
                        GROUP_CONCAT(
                                CASE WHEN goals_for > goals_against THEN 'W' WHEN goals_for = goals_against THEN 'D' ELSE 'L' END,
                                '')                                                                                 AS sequence
                 FROM (SELECT *
                       FROM (SELECT home_team_name  AS team_name,
                                    home_team_score AS goals_for,
                                    away_team_score AS goals_against,
                                    timestamp
                             FROM footy_stats_germany_bundesliga_200708_match
                             WHERE home_team_score IS NOT NULL
                             UNION ALL
                             SELECT away_team_name  AS team_name,
                                    away_team_score AS goals_for,
                                    home_team_score AS goals_against,
                                    timestamp
                             FROM footy_stats_germany_bundesliga_200708_match
                             WHERE away_team_score IS NOT NULL)
                       ORDER BY timestamp)
                 GROUP BY team_name
                 ORDER BY points DESC, goal_difference DESC, goals_for DESC, team_name);
        SQL
        );

        $this->addSql(<<<'SQL'
            CREATE VIEW footy_stats_germany_bundesliga_200708_home_team_standing AS
            SELECT ROW_NUMBER() OVER (ORDER BY 1) AS position, *
            FROM(SELECT team_name,
                        COUNT(team_name)                                                                                 AS matches_played,
                        SUM(CASE WHEN goals_for > goals_against THEN 1 ELSE 0 END)                                       AS wins,
                        SUM(CASE WHEN goals_for = goals_against THEN 1 ELSE 0 END)                                       AS draws,
                        SUM(CASE WHEN goals_for < goals_against THEN 1 ELSE 0 END)                                       AS losses,
                        SUM(goals_for)                                                                                   AS goals_for,
                        SUM(goals_for) / CAST(COUNT(team_name) AS FLOAT)                                                 AS goals_for_per_game,
                        SUM(goals_against)                                                                               AS goals_against,
                        SUM(goals_against) / CAST(COUNT(team_name) AS FLOAT)                                             AS goals_against_per_game,
                        SUM(goals_for) - SUM(goals_against)                                                              AS goal_difference,
                        SUM(CASE WHEN goals_for > goals_against THEN 3 WHEN goals_for = goals_against THEN 1 ELSE 0 END) AS points,
                        SUM(CASE WHEN goals_for > goals_against THEN 3 WHEN goals_for = goals_against THEN 1 ELSE 0 END) /
                        CAST(COUNT(team_name) AS FLOAT)                                                                  AS points_per_game,
                        GROUP_CONCAT(
                                CASE WHEN goals_for > goals_against THEN 'W' WHEN goals_for = goals_against THEN 'D' ELSE 'L' END,
                                '')                                                                                 AS sequence
                  FROM (SELECT *
                        FROM (SELECT home_team_name  AS team_name,
                                     home_team_score AS goals_for,
                                     away_team_score AS goals_against,
                                     timestamp
                              FROM footy_stats_germany_bundesliga_200708_match
                              WHERE home_team_score IS NOT NULL)
                        ORDER BY timestamp)
                  GROUP BY team_name
                  ORDER BY points DESC, goal_difference DESC, goals_for DESC, team_name);
        SQL
        );

        $this->addSql(<<<'SQL'
            CREATE VIEW footy_stats_germany_bundesliga_200708_away_team_standing AS
            SELECT ROW_NUMBER() OVER (ORDER BY 1) AS position, *
            FROM(SELECT team_name,
                        COUNT(team_name)                                                                                 AS matches_played,
                        SUM(CASE WHEN goals_for > goals_against THEN 1 ELSE 0 END)                                       AS wins,
                        SUM(CASE WHEN goals_for = goals_against THEN 1 ELSE 0 END)                                       AS draws,
                        SUM(CASE WHEN goals_for < goals_against THEN 1 ELSE 0 END)                                       AS losses,
                        SUM(goals_for)                                                                                   AS goals_for,
                        SUM(goals_for) / CAST(COUNT(team_name) AS FLOAT)                                                 AS goals_for_per_game,
                        SUM(goals_against)                                                                               AS goals_against,
                        SUM(goals_against) / CAST(COUNT(team_name) AS FLOAT)                                             AS goals_against_per_game,
                        SUM(goals_for) - SUM(goals_against)                                                              AS goal_difference,
                        SUM(CASE WHEN goals_for > goals_against THEN 3 WHEN goals_for = goals_against THEN 1 ELSE 0 END) AS points,
                        SUM(CASE WHEN goals_for > goals_against THEN 3 WHEN goals_for = goals_against THEN 1 ELSE 0 END) /
                        CAST(COUNT(team_name) AS FLOAT)                                                                  AS points_per_game,
                        GROUP_CONCAT(
                                CASE WHEN goals_for > goals_against THEN 'W' WHEN goals_for = goals_against THEN 'D' ELSE 'L' END,
                                '')                                                                                 AS sequence
                  FROM (SELECT *
                        FROM (SELECT away_team_name  AS team_name,
                                     away_team_score AS goals_for,
                                     home_team_score AS goals_against,
                                     timestamp
                              FROM footy_stats_germany_bundesliga_200708_match
                              WHERE away_team_score IS NOT NULL)
                        ORDER BY timestamp)
                  GROUP BY team_name
                  ORDER BY points DESC, goal_difference DESC, goals_for DESC, team_name);
        SQL
        );

        $this->addSql(<<<'SQL'
            CREATE VIEW footy_stats_germany_bundesliga_200708_team_strength AS
            SELECT team_name,
                   goals_for_per_game / (SELECT AVG(goals_for_per_game) FROM footy_stats_germany_bundesliga_200708_team_standing)         AS attack,
                   goals_against_per_game / (SELECT AVG(goals_against_per_game) FROM footy_stats_germany_bundesliga_200708_team_standing) AS defense
            FROM footy_stats_germany_bundesliga_200708_team_standing
            ORDER BY team_name;
        SQL
        );

        $this->addSql(<<<'SQL'
            CREATE VIEW footy_stats_germany_bundesliga_200708_match_xg AS
            SELECT home_team_name,
                   away_team_name,
                   (SELECT AVG(goals_for_per_game) FROM footy_stats_germany_bundesliga_200708_home_team_standing) * h.attack * a.defense AS home_team_xg,
                   (SELECT AVG(goals_for_per_game) FROM footy_stats_germany_bundesliga_200708_away_team_standing) * a.attack * h.defense AS away_team_xg,
                   timestamp,
                   extra
            FROM footy_stats_germany_bundesliga_200708_match AS m
                INNER JOIN footy_stats_germany_bundesliga_200708_team_strength AS h ON m.home_team_name = h.team_name
                INNER JOIN footy_stats_germany_bundesliga_200708_team_strength AS a ON m.away_team_name = a.team_name
            WHERE m.home_team_score IS NULL
            ORDER BY timestamp;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP VIEW footy_stats_germany_bundesliga_200708_match_xg;
        SQL
        );

        $this->addSql(<<<'SQL'
            DROP VIEW footy_stats_germany_bundesliga_200708_team_strength;
        SQL
        );

        $this->addSql(<<<'SQL'
            DROP VIEW footy_stats_germany_bundesliga_200708_away_team_standing;
        SQL
        );

        $this->addSql(<<<'SQL'
            DROP VIEW footy_stats_germany_bundesliga_200708_home_team_standing;
        SQL
        );

        $this->addSql(<<<'SQL'
            DROP VIEW footy_stats_germany_bundesliga_200708_team_standing;
        SQL
        );

        $this->addSql(<<<'SQL'
            DROP TABLE footy_stats_germany_bundesliga_200708_match;
        SQL
        );
    }
}
