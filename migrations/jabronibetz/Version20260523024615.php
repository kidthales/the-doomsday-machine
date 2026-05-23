<?php

declare(strict_types=1);

namespace DoctrineMigrations\Jabronibetz;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260523024615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the football_gender table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE football_gender (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL
            )
        SQL
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_FOOTBALL_GENDER_NAME ON football_gender (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE football_gender');
    }
}
