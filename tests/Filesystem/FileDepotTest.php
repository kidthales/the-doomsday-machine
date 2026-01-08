<?php

declare(strict_types=1);

namespace App\Tests\Filesystem;

use App\Filesystem\FileDepot;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FileDepotTest extends KernelTestCase
{
    public static function provide_test_makePath(): array
    {
        $base = '/app/data/test/file_depot';

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
}
