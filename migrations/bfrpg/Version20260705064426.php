<?php

declare(strict_types=1);

namespace DoctrineMigrations\BFRPG;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705064426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create/drop the rules_weapon table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE rules_weapon (
              id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
              rules_weapon_size_id INTEGER DEFAULT NULL,
              rules_weapon_category_id INTEGER DEFAULT NULL,
              rules_source_id INTEGER DEFAULT NULL,
              name VARCHAR(128) NOT NULL,
              price DOUBLE PRECISION NOT NULL,
              weight DOUBLE PRECISION NOT NULL,
              description CLOB DEFAULT NULL,
              damage_roll VARCHAR(16) DEFAULT NULL,
              missile_damage_roll VARCHAR(16) DEFAULT NULL,
              one_handed_damage_roll VARCHAR(16) DEFAULT NULL,
              two_handed_damage_roll VARCHAR(16) DEFAULT NULL,
              CONSTRAINT FK_CE4D0EC8215A218D FOREIGN KEY (rules_weapon_size_id) REFERENCES rules_weapon_size (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_CE4D0EC85364FE3E FOREIGN KEY (rules_weapon_category_id) REFERENCES rules_weapon_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
              CONSTRAINT FK_CE4D0EC86F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        SQL
        );
        $this->addSql('CREATE INDEX IDX_CE4D0EC8215A218D ON rules_weapon (rules_weapon_size_id)');
        $this->addSql('CREATE INDEX IDX_CE4D0EC85364FE3E ON rules_weapon (rules_weapon_category_id)');
        $this->addSql('CREATE INDEX IDX_CE4D0EC86F972CB7 ON rules_weapon (rules_source_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_RULES_WEAPON_NAME ON rules_weapon (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rules_weapon');
    }
}
