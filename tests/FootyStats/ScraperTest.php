<?php

declare(strict_types=1);

namespace App\Tests\FootyStats;

use App\FootyStats\Scraper;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ScraperTest extends KernelTestCase
{
    public function test_getNations(): void
    {
        self::bootKernel();

        /** @var Scraper $scraper */
        $scraper = self::getContainer()->get(Scraper::class);

        $actual = $scraper->getNations();

        self::assertContains('England', $actual);
        self::assertContains('Germany', $actual);
    }

    public static function provide_test_getCompetitions(): array
    {
        return [
            'England' => ['England', ['Premier League', 'Championship']],
            'Germany' => ['Germany', ['Bundesliga']],
        ];
    }

    #[DataProvider('provide_test_getCompetitions')]
    public function test_getCompetitions(string $subject, array $expected): void
    {
        self::bootKernel();

        /** @var Scraper $scraper */
        $scraper = self::getContainer()->get(Scraper::class);

        $actual = $scraper->getCompetitions($subject);

        self::assertCount(count($expected), $actual);

        sort($expected);
        sort($actual);

        for ($i = 0; $i < count($expected); ++$i) {
            self::assertSame($expected[$i], $actual[$i]);
        }
    }
}
