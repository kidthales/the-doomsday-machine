<?php
/*
 * The Doomsday Machine
 * Copyright (C) 2026  Tristan Bonsor
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\Migrations\Configuration\Configuration;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use function Symfony\Component\String\s;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Autoconfigure(public: true)]
final readonly class FootyStatsMigrationGenerator
{
    private const string ADD_SQL_TEMPLATE = <<<'PHP'
        $this->addSql(<<<'SQL'
<sql>
        SQL
        );
PHP;

    private const string MIGRATION_TEMPLATE = <<<'PHP'
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class <class_name> extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Footy Stats: <description>';
    }

    public function up(Schema $schema): void
    {
<up>
    }

    public function down(Schema $schema): void
    {
<down>
    }
}

PHP;

    public function __construct(
        #[Autowire(service: 'doctrine.migrations.configuration')] private Configuration $config,
        private Filesystem                                                              $filesystem,
    )
    {
    }

    public function generate(array $up, array $down, string $description): string
    {
        $up = array_map(fn($sql) => s(self::ADD_SQL_TEMPLATE)->replace('<sql>', $sql)->toString(), $up);
        $down = array_map(fn($sql) => s(self::ADD_SQL_TEMPLATE)->replace('<sql>', $sql)->toString(), $down);

        $date = date('YmdHis');
        $className = "Version$date";

        $path = $this->config->getMigrationDirectories()['DoctrineMigrations'] . '/' . $className . '.php';

        $content = s(self::MIGRATION_TEMPLATE)
            ->replace('<class_name>', $className)
            ->replace('<description>', $description)
            ->replace('<up>', implode(PHP_EOL . PHP_EOL, $up))
            ->replace('<down>', implode(PHP_EOL . PHP_EOL, $down))
            ->toString();

        // @codeCoverageIgnoreStart
        if ($this->filesystem->exists($path)) {
            throw new RuntimeException(sprintf('Migration file "%s" already exists', $path));
        }
        // @codeCoverageIgnoreEnd

        $this->filesystem->appendToFile($path, $content);

        return $path;
    }
}
