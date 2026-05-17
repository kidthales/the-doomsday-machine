<?php

declare(strict_types=1);

namespace DoctrineMigrations\Jabronibetz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260517043033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the football_organization table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE football_organization (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              acronym VARCHAR(32) NOT NULL
            )
        SQL
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_NAME ON football_organization (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE football_organization');
    }
}
