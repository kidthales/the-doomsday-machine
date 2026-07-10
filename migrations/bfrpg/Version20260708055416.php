<?php

declare(strict_types=1);

namespace DoctrineMigrations\BFRPG;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708055416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the rules_item_range_category_distance table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE rules_item_range_category_distance (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              rules_item_id INTEGER DEFAULT NULL,
              rules_range_category_id INTEGER DEFAULT NULL,
              rules_source_id INTEGER DEFAULT NULL,
              distance INTEGER NOT NULL,
              CONSTRAINT FK_5AE47021CE1E2138 FOREIGN KEY (rules_item_id) REFERENCES rules_item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_5AE4702195AF3846 FOREIGN KEY (rules_range_category_id) REFERENCES rules_range_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_5AE470216F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL
        );
        $this->addSql('CREATE INDEX IDX_5AE47021CE1E2138 ON rules_item_range_category_distance (rules_item_id)');
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5AE4702195AF3846 ON rules_item_range_category_distance (rules_range_category_id)
        SQL
        );
        $this->addSql('CREATE INDEX IDX_5AE470216F972CB7 ON rules_item_range_category_distance (rules_source_id)');
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_RULES_ITEM_RANGE_CATEGORY_DISTANCE_ITEM_ID_RANGE_CATEGORY_ID ON rules_item_range_category_distance (
              rules_item_id, rules_range_category_id
            )
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rules_item_range_category_distance');
    }
}
