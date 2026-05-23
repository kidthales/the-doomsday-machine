<?php

declare(strict_types=1);

namespace DoctrineMigrations\BFRPG;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522075347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the rules_source table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE rules_source (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_RULES_SOURCE_NAME ON rules_source (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rules_source');
    }
}
