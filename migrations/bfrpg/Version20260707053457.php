<?php

declare(strict_types=1);

namespace DoctrineMigrations\BFRPG;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260707053457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the rules_range_category table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE rules_range_category (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              rules_source_id INTEGER DEFAULT NULL,
              name VARCHAR(16) NOT NULL,
              modifier SMALLINT NOT NULL,
              CONSTRAINT FK_85F87F8C6F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL
        );
        $this->addSql('CREATE INDEX IDX_85F87F8C6F972CB7 ON rules_range_category (rules_source_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_RULES_RANGE_CATEGORY_NAME ON rules_range_category (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rules_range_category');
    }
}
