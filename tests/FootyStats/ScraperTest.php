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
                [new MockResponse(''), new MockResponse('')],
                ['current' => '', 'previous' => ['overview' => [], 'fixtures' => []]]
            ],
            'Mismatched Content' => [
                ['England', 'Championship'],
                [
                    new MockResponse(<<<'HTML'
<div id="teamSummary">
    <div class="season">
        <div class="drop-down-parent">
            2025/26
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="overview">2024/25</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="9663" data-hash="20232024" data-zzzz="form-table" data-zzz="overview">2023/24</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="7593" data-hash="20222023" data-zzzz="form-table" data-zzz="overview">2022/23</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="6089" data-hash="20212022" data-zzzz="form-table" data-zzz="overview">2021/22</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="4912" data-hash="20202021" data-zzzz="form-table" data-zzz="overview">2020/21</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="2187" data-hash="20192020" data-zzzz="form-table" data-zzz="overview">2019/20</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="1624" data-hash="20182019" data-zzzz="form-table" data-zzz="overview">2018/19</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="165" data-hash="20172018" data-zzzz="form-table" data-zzz="overview">2017/18</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="22" data-hash="20162017" data-zzzz="form-table" data-zzz="overview">2016/17</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="25" data-hash="20152016" data-zzzz="form-table" data-zzz="overview">2015/16</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="26" data-hash="20142015" data-zzzz="form-table" data-zzz="overview">2014/15</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="27" data-hash="20132014" data-zzzz="form-table" data-zzz="overview">2013/14</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="3141" data-hash="20122013" data-zzzz="form-table" data-zzz="overview">2012/13</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="3143" data-hash="20112012" data-zzzz="form-table" data-zzz="overview">2011/12</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="3146" data-hash="20102011" data-zzzz="form-table" data-zzz="overview">2010/11</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="3147" data-hash="20092010" data-zzzz="form-table" data-zzz="overview">2009/10</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="8031" data-hash="20082009" data-zzzz="form-table" data-zzz="overview">2008/09</a></li>
            </ul>
        </div>
    </div>
</div>
HTML),
                    new MockResponse('')
                ],
                new RuntimeException('Overview current season (2025/26) does not match fixtures current season ()')
            ],
            'Mismatched Content 2' => [
                ['England', 'Championship'],
                [
                    new MockResponse(<<<'HTML'
<div id="teamSummary">
    <div class="season">
        <div class="drop-down-parent">
            2025/26
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="overview">2024/25</a></li>
            </ul>
        </div>
    </div>
</div>
HTML),
                    new MockResponse(<<<'HTML'
<div id="teamSummary">
    <div class="season">
        <div class="drop-down-parent">
            2025/26
        </div>
    </div>
</div>
HTML)
                ],
                new RuntimeException('Overview previous seasons count (1) does not match fixtures previous seasons count (0)')
            ],
            'Mismatched Content 3' => [
                ['England', 'Championship'],
                [
                    new MockResponse(<<<'HTML'
<div id="teamSummary">
    <div class="season">
        <div class="drop-down-parent">
            2025/26
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="overview">2024/25</a></li>
            </ul>
        </div>
    </div>
</div>
HTML),
                    new MockResponse(<<<'HTML'
<div id="teamSummary">
    <div class="season">
        <div class="drop-down-parent">
            2025/26
            <li><a href="#" class="changeLeagueDataButton" data-z="8031" data-hash="20082009" data-zzzz="form-table" data-zzz="fixtures">2008/09</a></li>
        </div>
    </div>
</div>
HTML)
                ],
                new RuntimeException('Overview previous season (2024/25) does not match fixtures previous season (2008/09)')
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
