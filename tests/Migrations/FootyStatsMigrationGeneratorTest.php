<?php

declare(strict_types=1);

namespace App\Tests\Migrations;

use App\Migrations\FootyStatsMigrationGenerator;
use Doctrine\Migrations\Configuration\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use function Symfony\Component\String\s;

/**
 * @deprecated
 */
#[CoversClass(FootyStatsMigrationGenerator::class)]
final class FootyStatsMigrationGeneratorTest extends KernelTestCase
{
    public const string MIGRATIONS_PATH = '/app/data/test/file_depot/migrations';

    public function setUp(): void
    {
        mkdir(self::MIGRATIONS_PATH);
    }

    public function tearDown(): void
    {
        (new Filesystem())->remove(self::MIGRATIONS_PATH);
        parent::tearDown();
    }

    public static function provide_test_generate(): array
    {
        return [
            [
                [[], [], 'This is a test!'],
                <<<'PHP'
    public function getDescription(): string
    {
        return 'Footy Stats: This is a test!';
    }

    public function up(Schema $schema): void
    {

    }

    public function down(Schema $schema): void
    {

    }
PHP
            ],
            [
                [
                    ['Pretend this is SQL!', 'Pretend this is SQL!'],
                    ['Pretend this is SQL!', 'Pretend this is SQL!'],
                    'This is another test!'
                ],
                <<<'PHP'
    public function getDescription(): string
    {
        return 'Footy Stats: This is another test!';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
Pretend this is SQL!
        SQL
        );

        $this->addSql(<<<'SQL'
Pretend this is SQL!
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
Pretend this is SQL!
        SQL
        );

        $this->addSql(<<<'SQL'
Pretend this is SQL!
        SQL
        );
    }
PHP
            ]
        ];
    }

    #[DataProvider('provide_test_generate')]
    public function test_generate(array $subject, string $expected): void
    {
        self::bootKernel();

        $config = new Configuration();
        $config->addMigrationsDirectory('DoctrineMigrations', self::MIGRATIONS_PATH);
        self::getContainer()->set('doctrine.migrations.configuration', $config);

        /** @var FootyStatsMigrationGenerator $migrationGenerator */
        $migrationGenerator = self::getContainer()->get(FootyStatsMigrationGenerator::class);

        $actualPath = $migrationGenerator->generate(...$subject);

        self::assertMatchesRegularExpression(
            '/' . s(self::MIGRATIONS_PATH)->replace('/', '\/')->toString() . '\/Version\d{14}.php/',
            $actualPath
        );

        $actualContent = file_get_contents($actualPath);

        self::assertMatchesRegularExpression(
            '/final class Version\d{14} extends AbstractMigration/',
            $actualContent
        );

        self::assertStringContainsString($expected, $actualContent);
    }
}
