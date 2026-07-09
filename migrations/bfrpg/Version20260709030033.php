<?php

declare(strict_types=1);

namespace DoctrineMigrations\BFRPG;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709030033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the rules_weapon_range_category_distance table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE rules_weapon_range_category_distance (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              rules_weapon_id INTEGER DEFAULT NULL,
              rules_range_category_id INTEGER DEFAULT NULL,
              rules_source_id INTEGER DEFAULT NULL,
              distance INTEGER NOT NULL,
              CONSTRAINT FK_F5A5BFB66F1312A5 FOREIGN KEY (rules_weapon_id) REFERENCES rules_weapon (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_F5A5BFB695AF3846 FOREIGN KEY (rules_range_category_id) REFERENCES rules_range_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_F5A5BFB66F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL
        );
        $this->addSql('CREATE INDEX IDX_F5A5BFB66F1312A5 ON rules_weapon_range_category_distance (rules_weapon_id)');
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F5A5BFB695AF3846 ON rules_weapon_range_category_distance (rules_range_category_id)
        SQL
        );
        $this->addSql('CREATE INDEX IDX_F5A5BFB66F972CB7 ON rules_weapon_range_category_distance (rules_source_id)');
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_RULES_WEAPON_RANGE_CATEGORY_DISTANCE_WEAPON_ID_RANGE_CATEGORY_ID ON rules_weapon_range_category_distance (
              rules_weapon_id, rules_range_category_id
            )
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rules_weapon_range_category_distance');
    }
}
