<?php

declare(strict_types=1);

namespace App\Tests\FootyStats;

use App\FootyStats\Scraper;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class ScraperTest extends KernelTestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        (new Filesystem())->remove('/app/data/test/file_depot/footy_stats');
    }

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

    public static function provide_test_scrapeAvailableSeasons(): array
    {
        return [
            'Unsupported Nation' => [
                ['Unsupported Nation', ''],
                [new MockResponse('')],
                new RuntimeException('Unsupported nation: Unsupported Nation')
            ],
            'Unsupported Competition' => [
                ['England', 'Unsupported Competition'],
                [new MockResponse('')],
                new RuntimeException('Unsupported competition: Unsupported Competition')
            ],
            'Empty Content' => [
                ['England', 'Championship'],
                [new MockResponse(''), new MockResponse(''), new MockResponse('')],
                ['current' => '', 'previous' => ['overview' => [], 'fixtures' => []]]
            ]
        ];
    }

    #[DataProvider('provide_test_scrapeAvailableSeasons')]
    public function test_scrapeAvailableSeasons(array $subject, array $mockResponses, array|Throwable $expected): void
    {
        self::bootKernel();

        self::getContainer()->set('footy_stats.client', new MockHttpClient($mockResponses));

        /** @var Scraper $scraper */
        $scraper = self::getContainer()->get(Scraper::class);

        try {
            $actual = $scraper->scrapeAvailableSeasons(...$subject);
        } catch (Throwable $e) {
            if ($expected instanceof Throwable) {
                self::assertInstanceOf(get_class($expected), $e);
                self::assertStringContainsString($expected->getMessage(), $e->getMessage());
            } else {
                self::fail('Unexpected exception: ' . $e->getMessage());
            }
            return;
        }

        self::assertSame($expected['current'], $actual['current']);

        self::assertCount(count($expected['previous']['overview']), $actual['previous']['overview']);
        self::assertCount(count($expected['previous']['fixtures']), $actual['previous']['fixtures']);

        $expectedOverviewPreviousSeasons = array_keys($expected['previous']['overview']);
        $actualOverviewPreviousSeasons = array_keys($actual['previous']['overview']);

        sort($expectedOverviewPreviousSeasons);
        sort($actualOverviewPreviousSeasons);

        for ($i = 0; $i < count($expectedOverviewPreviousSeasons); ++$i) {
            self::assertSame($expectedOverviewPreviousSeasons[$i], $actualOverviewPreviousSeasons[$i]);
        }

        $expectedFixturesPreviousSeasons = array_keys($expected['previous']['fixtures']);
        $actualFixturesPreviousSeasons = array_keys($actual['previous']['fixtures']);

        sort($expectedFixturesPreviousSeasons);
        sort($actualFixturesPreviousSeasons);

        for ($i = 0; $i < count($expectedFixturesPreviousSeasons); ++$i) {
            self::assertSame($expectedFixturesPreviousSeasons[$i], $actualFixturesPreviousSeasons[$i]);
        }

        $actual2 = $scraper->scrapeAvailableSeasons(...$subject);

        self::assertSame($actual, $actual2);
    }
}
