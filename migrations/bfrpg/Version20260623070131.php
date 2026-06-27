<?php

declare(strict_types=1);

namespace DoctrineMigrations\BFRPG;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260623070131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the rules_weapon_size table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE rules_weapon_size (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              rules_source_id INTEGER DEFAULT NULL,
              name VARCHAR(16) NOT NULL,
              short_name VARCHAR(1) NOT NULL,
              CONSTRAINT FK_BE64640B6F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL
        );
        $this->addSql('CREATE INDEX IDX_BE64640B6F972CB7 ON rules_weapon_size (rules_source_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_RULES_WEAPON_SIZE_NAME ON rules_weapon_size (name)');
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_RULES_WEAPON_SIZE_SHORT_NAME ON rules_weapon_size (short_name)
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rules_weapon_size');
    }
}
