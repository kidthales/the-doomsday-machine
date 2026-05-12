<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Shared\Filesystem;

use App\Domain\Shared\Filesystem\FileDepot;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
#[CoversClass(FileDepot::class)]
final class FileDepotTest extends KernelTestCase
{
    public const string TEST_FILE_A = 'test_a.txt';
    public const string TEST_FILE_B = 'test_b.txt';
    public const string TEST_FILE_CONTENT = 'Hello World!';
    public const string NOT_FOUND_FILE = '__notfound__';

    private FileDepot|null $fileDepot = null;

    private string|null $fileDepotPath = null;

    private string|null $testDir = null;

    private string|null $testDirPath = null;

    public function setUp(): void
    {
        $this->bootKernel();
        $this->fileDepot = $this->getContainer()->get(FileDepot::class);
        $this->fileDepotPath = $this->getContainer()->getParameter('app.shared.filesystem.file_depot_path');
        $this->testDir = 'test_files_' . uniqid(more_entropy: true);
        $this->testDirPath = $this->fileDepotPath . DIRECTORY_SEPARATOR . $this->testDir;

        mkdir($this->testDirPath, 0777, true);
        touch($this->testDirPath . DIRECTORY_SEPARATOR . self::TEST_FILE_A);
        file_put_contents($this->testDirPath . DIRECTORY_SEPARATOR . self::TEST_FILE_B, self::TEST_FILE_CONTENT);
    }

    public function tearDown(): void
    {
        foreach ([self::TEST_FILE_A, self::TEST_FILE_B, self::NOT_FOUND_FILE] as $file) {
            $fullPath = $this->testDirPath . DIRECTORY_SEPARATOR . $file;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        if (is_dir($this->testDirPath)) {
            rmdir($this->testDirPath);
        }

        $this->testDirPath = null;
        $this->fileDepotPath = null;
        $this->fileDepot = null;

        parent::tearDown();
    }

    #[Test]
    #[TestWith(['test', '/test'], 'test')]
    #[TestWith(['/test', '/test'], '/test')]
    #[TestWith(['test/', '/test'], 'test/')]
    #[TestWith(['/testA/testB', '/testA/testB'], '/testA/testB')]
    #[TestWith(['testA/testB/', '/testA/testB'], 'testA/testB/')]
    #[TestWith(['///testA///testB//', '/testA/testB'], '///testA///testB//')]
    public function it_makes_a_path_within_the_file_depot(string $subject, string $expected): void
    {
        $this->assertSame($this->fileDepotPath . $expected, $this->fileDepot->makePath($subject));
    }

    #[Test]
    #[TestWith([self::TEST_FILE_A, true], self::TEST_FILE_A)]
    #[TestWith([self::NOT_FOUND_FILE, false], self::NOT_FOUND_FILE)]
    #[TestWith([[self::TEST_FILE_A, self::TEST_FILE_B], true], self::TEST_FILE_A . ', ' . self::TEST_FILE_B)]
    #[TestWith(
        [[self::TEST_FILE_A, self::NOT_FOUND_FILE, self::TEST_FILE_B], false],
        self::TEST_FILE_A . ', ' . self::NOT_FOUND_FILE . ', ' . self::TEST_FILE_B
    )]
    public function it_confirms_that_files_exist_in_the_file_depot(
        iterable|string $subject,
        bool $expected
    ): void
    {
        if (is_array($subject)) {
            $subject = array_map(fn (mixed $file) => $this->testDir . DIRECTORY_SEPARATOR . $file, $subject);
        } else {
            $subject = $this->testDir . DIRECTORY_SEPARATOR . $subject;
        }

        $this->assertSame($expected, $this->fileDepot->exists($subject));
    }

    #[Test]
    #[TestWith(
        [[self::TEST_FILE_A, self::TEST_FILE_B], [true, true], [false, false]],
        self::TEST_FILE_A . ', ' . self::TEST_FILE_B
    )]
    public function it_removes_files_from_the_file_depot(
        iterable|string $subject,
        array|bool $expectedBefore,
        array|bool $expectedAfter
    ): void
    {
        $files = is_iterable($subject) ? $subject : [$subject];
        $expectedBefore = is_array($expectedBefore) ? $expectedBefore : [$expectedBefore];
        $expectedAfter = is_array($expectedAfter) ? $expectedAfter : [$expectedAfter];

        foreach ($files as $ix => $file) {
            $this->assertSame(file_exists($this->testDirPath . DIRECTORY_SEPARATOR . $file), $expectedBefore[$ix]);
        }

        if (is_array($subject)) {
            $subject = array_map(fn (mixed $file) => $this->testDir . DIRECTORY_SEPARATOR . $file, $subject);
        } else {
            $subject = $this->testDir . DIRECTORY_SEPARATOR . $subject;
        }

        $this->fileDepot->remove($subject);

        foreach ($files as $ix => $file) {
            $this->assertSame(file_exists($this->testDirPath . DIRECTORY_SEPARATOR . $file), $expectedAfter[$ix]);
        }
    }

    #[Test]
    #[TestWith([[self::NOT_FOUND_FILE, self::TEST_FILE_CONTENT], false, self::TEST_FILE_CONTENT], self::NOT_FOUND_FILE)]
    #[TestWith([[self::TEST_FILE_A, self::TEST_FILE_CONTENT], '', self::TEST_FILE_CONTENT], self::TEST_FILE_A)]
    #[TestWith(
        [
            [self::TEST_FILE_B, self::TEST_FILE_CONTENT],
            self::TEST_FILE_CONTENT,
            self::TEST_FILE_CONTENT . self::TEST_FILE_CONTENT
        ],
        self::TEST_FILE_B
    )]
    public function it_appends_content_to_a_file_in_the_file_depot(
        array $subject,
        string|bool $expectedBefore,
        string $expectedAfter
    ): void
    {
        $this->assertSame($expectedBefore, file_get_contents($this->testDirPath . DIRECTORY_SEPARATOR . $subject[0]));
        $this->fileDepot->appendToFile($this->testDir . DIRECTORY_SEPARATOR . $subject[0], $subject[1]);
        $this->assertSame($expectedAfter, file_get_contents($this->testDirPath . DIRECTORY_SEPARATOR . $subject[0]));
    }

    #[Test]
    #[TestWith([self::TEST_FILE_A, ''], self::TEST_FILE_A)]
    #[TestWith([self::TEST_FILE_B, self::TEST_FILE_CONTENT], self::TEST_FILE_B)]
    public function it_reads_content_from_a_file_in_the_file_depot(string $subject, string $expected): void
    {
        $this->assertSame($expected, $this->fileDepot->readFile($this->testDir . DIRECTORY_SEPARATOR . $subject));
    }

    #[Test]
    public function it_throws_exception_when_reading_file_that_does_not_exist(): void
    {
        $this->expectException(IOException::class);
        $this->fileDepot->readFile($this->testDir . DIRECTORY_SEPARATOR . self::NOT_FOUND_FILE);
    }

    #[Test]
    #[TestWith([self::NOT_FOUND_FILE, false], self::NOT_FOUND_FILE)]
    #[TestWith([self::TEST_FILE_A, 0], self::TEST_FILE_A)]
    #[TestWith([self::TEST_FILE_B, 0], self::TEST_FILE_B)]
    public function it_can_check_the_modified_time_of_a_file_in_the_file_depot(string $subject, int|false $expected): void
    {
        $actual = $this->fileDepot->filemtime($this->testDir . DIRECTORY_SEPARATOR . $subject);

        if ($expected === false) {
            $this->assertFalse($actual);
        } else {
            $this->assertIsInt($actual);
        }
    }

    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->assertTrue(
            $this->getContainer()->has(FileDepot::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            FileDepot::class,
            $this->getContainer()->get(FileDepot::class),
            'The service must resolve to a valid FileDepot instance.'
        );
    }
}
