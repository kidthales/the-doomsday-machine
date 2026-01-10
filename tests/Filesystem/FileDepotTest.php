<?php

declare(strict_types=1);

namespace App\Tests\Filesystem;

use App\Filesystem\FileDepot;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Exception\IOException;
use Throwable;

final class FileDepotTest extends KernelTestCase
{
    public const string FILE_DEPOT_PATH = '/app/data/test/file_depot';
    public const string TEST_DIR_NAME = 'test_files';
    public const string TEST_DIR_PATH = self::TEST_DIR_NAME;
    public const string TEST_FILE_PATH_A = self::TEST_DIR_PATH . '/test_a.txt';
    public const string TEST_FILE_PATH_B = self::TEST_DIR_PATH . '/test_b.txt';
    public const string TEST_FILE_CONTENT = 'Hello World!';
    public const string NOT_FOUND_FILE_PATH = self::TEST_DIR_PATH . '/__notfound__';

    public function setUp(): void
    {
        mkdir(self::FILE_DEPOT_PATH . '/' . self::TEST_DIR_PATH);
        touch(self::FILE_DEPOT_PATH . '/' . self::TEST_FILE_PATH_A);
        file_put_contents(self::FILE_DEPOT_PATH . '/' . self::TEST_FILE_PATH_B, self::TEST_FILE_CONTENT);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unlink(self::FILE_DEPOT_PATH . '/' . self::NOT_FOUND_FILE_PATH);
        unlink(self::FILE_DEPOT_PATH . '/' . self::TEST_FILE_PATH_B);
        unlink(self::FILE_DEPOT_PATH . '/' . self::TEST_FILE_PATH_A);
        rmdir(self::FILE_DEPOT_PATH . '/' . self::TEST_DIR_PATH);
    }

    public static function provide_test_makePath(): array
    {
        $base = self::FILE_DEPOT_PATH;

        return [
            'single part, no slashes' => ['test', "$base/test"],
            'single part, leading slash' => ['/test', "$base/test"],
            'single part, trailing slash' => ['test/', "$base/test"],
            'multiple parts, leading slash' => ['/testA/testB', "$base/testA/testB"],
            'multiple parts, trailing slash' => ['testA/testB/', "$base/testA/testB"],
            'multiple parts, multiple slashes' => ['///testA///testB//', "$base/testA/testB"],
        ];
    }

    #[DataProvider('provide_test_makePath')]
    public function test_makePath(string $subject, string $expected): void
    {
        self::bootKernel();

        /** @var FileDepot $fileDepot */
        $fileDepot = self::getContainer()->get(FileDepot::class);

        $actual = $fileDepot->makePath($subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_exists(): array
    {
        $testFilePathA = self::TEST_FILE_PATH_A;
        $testFilePathB = self::TEST_FILE_PATH_B;
        $notFoundFilePath = self::NOT_FOUND_FILE_PATH;

        return [
            'file exists' => [$testFilePathA, true],
            'file does not exist' => [$notFoundFilePath, false],
            'multiple files exist' => [[$testFilePathA, $testFilePathB], true],
            'multiple files, some does not exist' => [[$testFilePathA, $notFoundFilePath, $testFilePathB], false]
        ];
    }

    #[DataProvider('provide_test_exists')]
    public function test_exists(iterable|string $subject, bool $expected): void
    {
        self::bootKernel();

        /** @var FileDepot $fileDepot */
        $fileDepot = self::getContainer()->get(FileDepot::class);

        $actual = $fileDepot->exists($subject);
        self::assertSame($expected, $actual);
    }

    public static function provide_test_remove(): array
    {
        return [
            'multiple files' => [[self::TEST_FILE_PATH_A, self::TEST_FILE_PATH_B], [true, true], [false, false]],
        ];
    }

    #[DataProvider('provide_test_remove')]
    public function test_remove(iterable|string $subject, array|bool $expectedBefore, array|bool $expectedAfter): void
    {
        self::bootKernel();

        $subject = is_iterable($subject) ? $subject : [$subject];
        $expectedBefore = is_array($expectedBefore) ? $expectedBefore : [$expectedBefore];
        $expectedAfter = is_array($expectedAfter) ? $expectedAfter : [$expectedAfter];

        self::assertCount(count($expectedBefore), $subject);
        self::assertCount(count($expectedAfter), $subject);

        $ix = 0;
        foreach ($subject as $filePath) {
            self::assertSame(file_exists(self::FILE_DEPOT_PATH . '/' . $filePath), $expectedBefore[$ix++]);
        }

        /** @var FileDepot $fileDepot */
        $fileDepot = self::getContainer()->get(FileDepot::class);
        $fileDepot->remove($subject);

        $ix = 0;
        foreach ($subject as $filePath) {
            self::assertSame(file_exists(self::FILE_DEPOT_PATH . '/' . $filePath), $expectedAfter[$ix++]);
        }
    }

    public static function provide_test_appendToFile(): array
    {
        $content = self::TEST_FILE_CONTENT;

        return [
            'new file' => [[self::NOT_FOUND_FILE_PATH, $content], false, $content],
            'existing file' => [[self::TEST_FILE_PATH_A, $content], '', $content],
            'existing file 2' => [[self::TEST_FILE_PATH_B, $content], $content, "$content$content"],
        ];
    }

    #[DataProvider('provide_test_appendToFile')]
    public function test_appendToFile(array $subject, string|bool $expectedBefore, string $expectedAfter): void
    {
        self::bootKernel();

        self::assertSame($expectedBefore, file_get_contents(self::FILE_DEPOT_PATH . '/' . $subject[0]));

        /** @var FileDepot $fileDepot */
        $fileDepot = self::getContainer()->get(FileDepot::class);
        $fileDepot->appendToFile(...$subject);

        self::assertSame($expectedAfter, file_get_contents(self::FILE_DEPOT_PATH . '/' . $subject[0]));
    }

    public static function provide_test_readFile(): array
    {
        return [
            'not found' => [self::NOT_FOUND_FILE_PATH, new IOException('test')],
            'existing file' => [self::TEST_FILE_PATH_A, ''],
            'existing file 2' => [self::TEST_FILE_PATH_B, self::TEST_FILE_CONTENT]
        ];
    }

    /**
     * @param string $subject
     * @param string|Throwable $expected
     * @return void
     * @throws Throwable
     */
    #[DataProvider('provide_test_readFile')]
    public function test_readFile(string $subject, string|Throwable $expected): void
    {
        self::bootKernel();

        /** @var FileDepot $fileDepot */
        $fileDepot = self::getContainer()->get(FileDepot::class);

        try {
            $actual = $fileDepot->readFile($subject);
        } catch (Throwable $e) {
            if ($expected instanceof Throwable) {
                self::assertInstanceOf(get_class($expected), $e);
            } else {
                throw $e;
            }
            return;
        }

        if ($expected instanceof Throwable) {
            self::fail('Expected exception to be thrown');
        }

        self::assertSame($expected, $actual);
    }

    public static function provide_test_filemtime(): array
    {
        return [
            'not found' => [self::NOT_FOUND_FILE_PATH, false],
            'existing file' => [self::TEST_FILE_PATH_A, null],
            'existing file 2' => [self::TEST_FILE_PATH_B, null]
        ];
    }

    #[DataProvider('provide_test_filemtime')]
    public function test_filemtime(string $subject, null|false $expected): void
    {
        self::bootKernel();

        /** @var FileDepot $fileDepot */
        $fileDepot = self::getContainer()->get(FileDepot::class);

        $actual = $fileDepot->filemtime($subject);

        if ($expected === null) {
            self::assertIsInt($actual);
        } else {
            self::assertFalse($actual);
        }
    }
}
