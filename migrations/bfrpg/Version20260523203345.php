<?php

declare(strict_types=1);

namespace DoctrineMigrations\BFRPG;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260523203345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the rules_item table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE rules_item (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              rules_source_id INTEGER DEFAULT NULL,
              name VARCHAR(128) NOT NULL,
              price DOUBLE PRECISION NOT NULL,
              weight DOUBLE PRECISION NOT NULL,
              description CLOB DEFAULT NULL,
              CONSTRAINT FK_FC62B0886F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL
        );
        $this->addSql('CREATE INDEX IDX_FC62B0886F972CB7 ON rules_item (rules_source_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_RULES_ITEM_NAME ON rules_item (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rules_item');
    }
}
