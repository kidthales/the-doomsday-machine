<?php

declare(strict_types=1);

namespace DoctrineMigrations\BFRPG;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710064524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the rules_armor table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE rules_armor (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              rules_source_id INTEGER DEFAULT NULL,
              name VARCHAR(128) NOT NULL,
              price DOUBLE PRECISION NOT NULL,
              weight DOUBLE PRECISION NOT NULL,
              description CLOB DEFAULT NULL,
              ac SMALLINT DEFAULT NULL,
              ac_bonus SMALLINT DEFAULT NULL,
              CONSTRAINT FK_A6A8B1186F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL
        );
        $this->addSql('CREATE INDEX IDX_A6A8B1186F972CB7 ON rules_armor (rules_source_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_RULES_ARMOR_NAME ON rules_armor (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rules_armor');
    }
}
