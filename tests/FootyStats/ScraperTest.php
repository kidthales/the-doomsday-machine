<?php

declare(strict_types=1);

namespace App\Tests\FootyStats;

use App\FootyStats\EndpointPayload;
use App\FootyStats\Scraper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

#[CoversClass(Scraper::class)]
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
            ],
            'Valid Content' => [
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
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="9663" data-hash="20232024" data-zzzz="form-table" data-zzz="fixtures">2023/24</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="7593" data-hash="20222023" data-zzzz="form-table" data-zzz="fixtures">2022/23</a></li>
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="fixtures">2024/25</a></li>
            </ul>
        </div>
    </div>
</div>
HTML)
                ],
                [
                    'current' => '2025/26',
                    'previous' => [
                        'overview' => [
                            '2024/25' => new EndpointPayload(),
                            '2023/24' => new EndpointPayload(),
                            '2022/23' => new EndpointPayload()
                        ],
                        'fixtures' => [
                            '2024/25' => new EndpointPayload(),
                            '2023/24' => new EndpointPayload(),
                            '2022/23' => new EndpointPayload()
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Throwable
     * @throws ServerExceptionInterface
     */
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
               throw $e;
            }
            return;
        }

        if ($expected instanceof Throwable) {
            self::fail('Expected exception to be thrown');
        }

        self::assertSame($expected['current'], $actual['current']);

        self::assertCount(count($expected['previous']['overview']), $actual['previous']['overview']);
        self::assertCount(count($expected['previous']['fixtures']), $actual['previous']['fixtures']);

        $expectedOverviewPreviousSeasons = array_keys($expected['previous']['overview']);
        $actualOverviewPreviousSeasons = array_keys($actual['previous']['overview']);

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

    public static function provide_test_scrapeTeamNames(): array
    {
        return [
            'Unsupported Nation' => [
                ['Unsupported Nation', '', ''],
                [new MockResponse('')],
                new RuntimeException('Unsupported nation: Unsupported Nation')
            ],
            'Unsupported Competition' => [
                ['England', 'Unsupported Competition', ''],
                [new MockResponse('')],
                new RuntimeException('Unsupported competition: Unsupported Competition')
            ],
            'Empty Content' => [
                ['England', 'Championship', '2025/26'],
                [new MockResponse(''), new MockResponse('')],
                new RuntimeException('Unsupported season: 2025/26')
            ],
            'Empty Content 2' => [
                ['England', 'Championship', '2025/26'],
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
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="fixtures">2024/25</a></li>
            </ul>
        </div>
    </div>
</div>
HTML),
                ],
                []
            ],
            'Mismatched Counts' => [
                ['England', 'Championship', '2025/26'],
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
<table>
    <tbody>
        <tr><td></td><td></td><td><a href="/clubs/coventry-city-fc-239">Coventry City FC</a></td></tr>
        <tr><td></td><td></td><td><a href="/clubs/middlesbrough-fc-147">Middlesbrough FC</a></td></tr>
        <tr><td></td><td></td><td><a href="/clubs/ipswich-town-fc-220">Ipswich Town FC</a></td></tr>
    </tbody>
</table>
HTML),
                    new MockResponse(<<<'HTML'
<div id="teamSummary">
    <div class="season">
        <div class="drop-down-parent">
            2025/26
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="fixtures">2024/25</a></li>
            </ul>
        </div>
    </div>
</div>
<table>
    <tbody class="leagueTable">
        <tr><td class="leagueTableTeamName"><a href="/clubs/coventry-city-fc-239"> Coventry City</a></td></tr>
        <tr><td class="leagueTableTeamName"><a href="/clubs/middlesbrough-fc-147"> Middlesbrough</a></td></tr>
    </tbody>
</table>
HTML),
                ],
                new RuntimeException('Overview team name count (3) does not match fixtures team name count (2)')
            ],
            'Mismatched Team href' => [
                ['England', 'Championship', '2025/26'],
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
<table>
    <tbody>
        <tr><td></td><td></td><td><a href="/clubs/coventry-city-fc-239">Coventry City FC</a></td></tr>
        <tr><td></td><td></td><td><a href="/clubs/middlesbrough-fc-147">Middlesbrough FC</a></td></tr>
        <tr><td></td><td></td><td><a href="/clubs/ipswich-town-fc-220">Ipswich Town FC</a></td></tr>
    </tbody>
</table>
HTML),
                    new MockResponse(<<<'HTML'
<div id="teamSummary">
    <div class="season">
        <div class="drop-down-parent">
            2025/26
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="fixtures">2024/25</a></li>
            </ul>
        </div>
    </div>
</div>
<table>
    <tbody class="leagueTable">
        <tr><td class="leagueTableTeamName"><a href="/clubs/coventry-city-fc-239"> Coventry City</a></td></tr>
        <tr><td class="leagueTableTeamName"><a href="/clubs/middlesbrough-fc-147"> Middlesbrough</a></td></tr>
        <tr><td class="leagueTableTeamName"><a href="/clubs/ipswich-town-fc-1337"> Ipswich Town</a></td></tr>
    </tbody>
</table>
HTML),
                ],
                new RuntimeException('Overview team name href (/clubs/ipswich-town-fc-220) does not match fixtures team name href (/clubs/ipswich-town-fc-1337)'),
            ],
            'Valid Content, Current Season' => [
                ['England', 'Championship', '2025/26'],
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
<table>
    <tbody>
        <tr><td></td><td></td><td><a href="/clubs/coventry-city-fc-239">Coventry City FC</a></td></tr>
        <tr><td></td><td></td><td><a href="/clubs/middlesbrough-fc-147">Middlesbrough FC</a></td></tr>
        <tr><td></td><td></td><td><a href="/clubs/ipswich-town-fc-220">Ipswich Town FC</a></td></tr>
    </tbody>
</table>
HTML),
                    new MockResponse(<<<'HTML'
<div id="teamSummary">
    <div class="season">
        <div class="drop-down-parent">
            2025/26
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="fixtures">2024/25</a></li>
            </ul>
        </div>
    </div>
</div>
<table>
    <tbody class="leagueTable">
        <tr><td class="leagueTableTeamName"><a href="/clubs/coventry-city-fc-239"> Coventry City</a></td></tr>
        <tr><td class="leagueTableTeamName"><a href="/clubs/middlesbrough-fc-147"> Middlesbrough</a></td></tr>
        <tr><td class="leagueTableTeamName"><a href="/clubs/ipswich-town-fc-220"> Ipswich Town</a></td></tr>
    </tbody>
</table>
HTML),
                ],
                [
                    ['Coventry City FC', 'Coventry City'],
                    ['Ipswich Town FC', 'Ipswich Town'],
                    ['Middlesbrough FC', 'Middlesbrough']
                ],
            ],
            'Valid Content, Previous Season' => [
                ['England', 'Championship', '2024/25'],
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
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="fixtures">2024/25</a></li>
            </ul>
        </div>
    </div>
</div>
HTML),
                    new MockResponse(<<<'HTML'
<table>
    <tbody>
        <tr><td></td><td></td><td><a href="/clubs/coventry-city-fc-239">Coventry City FC</a></td></tr>
        <tr><td></td><td></td><td><a href="/clubs/middlesbrough-fc-147">Middlesbrough FC</a></td></tr>
        <tr><td></td><td></td><td><a href="/clubs/ipswich-town-fc-220">Ipswich Town FC</a></td></tr>
    </tbody>
</table>
HTML),
                    new MockResponse(<<<'HTML'
<table>
    <tbody class="leagueTable">
        <tr><td class="leagueTableTeamName"><a href="/clubs/coventry-city-fc-239"> Coventry City</a></td></tr>
        <tr><td class="leagueTableTeamName"><a href="/clubs/middlesbrough-fc-147"> Middlesbrough</a></td></tr>
        <tr><td class="leagueTableTeamName"><a href="/clubs/ipswich-town-fc-220"> Ipswich Town</a></td></tr>
    </tbody>
</table>
HTML)
                ],
                [
                    ['Coventry City FC', 'Coventry City'],
                    ['Ipswich Town FC', 'Ipswich Town'],
                    ['Middlesbrough FC', 'Middlesbrough']
                ],
            ]
        ];
    }

    /**
     * @param array $subject
     * @param array $mockResponses
     * @param array|Throwable $expected
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws Throwable
     * @throws TransportExceptionInterface
     */
    #[DataProvider('provide_test_scrapeTeamNames')]
    public function test_scrapeTeamNames(array $subject, array $mockResponses, array|Throwable $expected): void
    {
        self::bootKernel();
        self::getContainer()->set('footy_stats.client', new MockHttpClient($mockResponses));

        /** @var Scraper $scraper */
        $scraper = self::getContainer()->get(Scraper::class);

        try {
            $actual = $scraper->scrapeTeamNames(...$subject);
        } catch (Throwable $e) {
            if ($expected instanceof Throwable) {
                self::assertInstanceOf(get_class($expected), $e);
                self::assertStringContainsString($expected->getMessage(), $e->getMessage());
            } else {
                throw $e;
            }
            return;
        }

        if ($expected instanceof Throwable) {
            self::fail('Expected exception to be thrown');
        }

        self::assertCount(count($expected), $actual);

        for ($i = 0; $i < count($expected); ++$i) {
            self::assertCount(2, $actual[$i]);
            self::assertSame($expected[$i][0], $actual[$i][0]);
            self::assertSame($expected[$i][1], $actual[$i][1]);
        }

        $actual2 = $scraper->scrapeTeamNames(...$subject);

        self::assertCount(count($expected), $actual2);

        for ($i = 0; $i < count($expected); ++$i) {
            self::assertCount(2, $actual[$i]);
            self::assertSame($expected[$i][0], $actual2[$i][0]);
            self::assertSame($expected[$i][1], $actual2[$i][1]);
        }
    }

    public static function provide_test_scrapeMatches(): array
    {
        return [
            'Unsupported Nation' => [
                ['Unsupported Nation', '', ''],
                [new MockResponse('')],
                new RuntimeException('Unsupported nation: Unsupported Nation')
            ],
            'Unsupported Competition' => [
                ['England', 'Unsupported Competition', ''],
                [new MockResponse('')],
                new RuntimeException('Unsupported competition: Unsupported Competition')
            ],
            'Empty Content' => [
                ['England', 'Championship', '2025/26'],
                [new MockResponse(''), new MockResponse('')],
                new RuntimeException('Unsupported season: 2025/26')
            ],
            'Empty Content 2' => [
                ['England', 'Championship', '2025/26'],
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
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="fixtures">2024/25</a></li>
            </ul>
        </div>
    </div>
</div>
HTML),
                ],
                []
            ],
            'Valid Content, Current Season' => [
                ['England', 'Championship', '2025/26'],
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
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="fixtures">2024/25</a></li>
            </ul>
        </div>
    </div>
</div>
<div id="matches-list">
    <ul class="match row">
        <li data-time="1768593600"></li>
        <li class="match-info row cf fl rfnone">
            <a href="/clubs/west-bromwich-albion-fc-142" class="team home fl">
                <div class="fr">
                    <span class="hover-modal-parent hover-modal-ajax-team" data-modal-offset="15" data-team-id="142" data-comp-id="14930">
                        West Bromwich Albion
                    </span>
                    <div class="form-box good">1.83</div>
                </div>
            </a>
            <a href="/england/west-bromwich-albion-fc-vs-middlesbrough-fc-h2h-stats" class="h2h-link pr fl">
                <i class="fas fa-chart-area"></i>
                <span class="ft-indicator blue">Stats</span>
            </a>
            <a href="/clubs/middlesbrough-fc-147" class="team away fl">
                <div class="form-box okay1">1.46</div>
                <span class="hover-modal-parent hover-modal-ajax-team" data-modal-offset="15" data-team-id="147" data-comp-id="14930">
                    Middlesbrough
                </span>
            </a>
        </li>
    </ul>
    <ul class="match row">
        <li class="date convert-months time">
            <div class="used-to-be-a">
                <span class="timezone-convert-match-week" data-time="1767643200">Mon 5, 12:00pm</span>
                <span class="" data-match-status="complete" data-match-time="1767643200"></span>
            </div>
        </li>
        <li class="match-info row cf fl rfnone">
            <a href="/clubs/leicester-city-fc-108" class="team home fl">
                <div class="fr">
                    <span class="hover-modal-parent hover-modal-ajax-team" data-modal-offset="15" data-team-id="108" data-comp-id="14930">
                        Leicester City
                    </span>
                    <div class="form-box good">1.69</div>
                </div></a>
            <a href="/england/leicester-city-fc-vs-west-bromwich-albion-fc-h2h-stats#8201877" class="h2h-link pr fl">
                <span class="bold ft-score">2 - 1</span>
                <span class="ft-indicator black">FT</span>
            </a>
            <a href="/clubs/west-bromwich-albion-fc-142" class="team away fl">
                <div class="form-box bad2">0.64</div>
                <span class="hover-modal-parent hover-modal-ajax-team" data-modal-offset="15" data-team-id="142" data-comp-id="14930">
                    West Bromwich Albion
                </span>
            </a>
        </li>
    </ul>
</div>
HTML),
                ],
                [
                    [
                        'timestamp' => 1768593600,
                        'home_team_name' => 'West Bromwich Albion',
                        'away_team_name' => 'Middlesbrough',
                        'home_team_score' => null,
                        'away_team_score' => null,
                        'extra' => null,
                    ],
                    [
                        'timestamp' => 1767643200,
                        'home_team_name' => 'Leicester City',
                        'away_team_name' => 'West Bromwich Albion',
                        'home_team_score' => 2,
                        'away_team_score' => 1,
                        'extra' => null,
                    ]
                ]
            ],
            'Valid Content, Previous Season' => [
                ['England', 'Championship', '2024/25'],
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
            <ul class="drop-down">
                <li><a href="#" class="changeLeagueDataButton" data-z="12451" data-hash="20242025" data-zzzz="form-table" data-zzz="fixtures">2024/25</a></li>
            </ul>
        </div>
    </div>
</div>
HTML),
                    new MockResponse(''),
                    new MockResponse(<<<'HTML'
<div id="matches-list">
    <ul class="match row">
        <li data-time="1768593600"></li>
        <li class="match-info row cf fl rfnone">
            <a href="/clubs/west-bromwich-albion-fc-142" class="team home fl">
                <div class="fr">
                    <span class="hover-modal-parent hover-modal-ajax-team" data-modal-offset="15" data-team-id="142" data-comp-id="14930">
                        West Bromwich Albion
                    </span>
                    <div class="form-box good">1.83</div>
                </div>
            </a>
            <a href="/england/west-bromwich-albion-fc-vs-middlesbrough-fc-h2h-stats" class="h2h-link pr fl">
                <i class="fas fa-chart-area"></i>
                <span class="ft-indicator blue">Stats</span>
            </a>
            <a href="/clubs/middlesbrough-fc-147" class="team away fl">
                <div class="form-box okay1">1.46</div>
                <span class="hover-modal-parent hover-modal-ajax-team" data-modal-offset="15" data-team-id="147" data-comp-id="14930">
                    Middlesbrough
                </span>
            </a>
        </li>
    </ul>
    <ul class="match row">
        <li class="date convert-months time">
            <div class="used-to-be-a">
                <span class="timezone-convert-match-week" data-time="1767643200">Mon 5, 12:00pm</span>
                <span class="" data-match-status="complete" data-match-time="1767643200"></span>
            </div>
        </li>
        <li class="match-info row cf fl rfnone">
            <a href="/clubs/leicester-city-fc-108" class="team home fl">
                <div class="fr">
                    <span class="hover-modal-parent hover-modal-ajax-team" data-modal-offset="15" data-team-id="108" data-comp-id="14930">
                        Leicester City
                    </span>
                    <div class="form-box good">1.69</div>
                </div></a>
            <a href="/england/leicester-city-fc-vs-west-bromwich-albion-fc-h2h-stats#8201877" class="h2h-link pr fl">
                <span class="bold ft-score">2 - 1</span>
                <span class="ft-indicator black">FT</span>
            </a>
            <a href="/clubs/west-bromwich-albion-fc-142" class="team away fl">
                <div class="form-box bad2">0.64</div>
                <span class="hover-modal-parent hover-modal-ajax-team" data-modal-offset="15" data-team-id="142" data-comp-id="14930">
                    West Bromwich Albion
                </span>
            </a>
        </li>
    </ul>
</div>
HTML)
                ],
                [
                    [
                        'timestamp' => 1768593600,
                        'home_team_name' => 'West Bromwich Albion',
                        'away_team_name' => 'Middlesbrough',
                        'home_team_score' => null,
                        'away_team_score' => null,
                        'extra' => null,
                    ],
                    [
                        'timestamp' => 1767643200,
                        'home_team_name' => 'Leicester City',
                        'away_team_name' => 'West Bromwich Albion',
                        'home_team_score' => 2,
                        'away_team_score' => 1,
                        'extra' => null,
                    ]
                ]
            ]
        ];
    }

    /**
     * @param array $subject
     * @param array $mockResponses
     * @param array|Throwable $expected
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws Throwable
     * @throws TransportExceptionInterface
     */
    #[DataProvider('provide_test_scrapeMatches')]
    public function test_scrapeMatches(array $subject, array $mockResponses, array|Throwable $expected): void
    {
        self::bootKernel();
        self::getContainer()->set('footy_stats.client', new MockHttpClient($mockResponses));

        /** @var Scraper $scraper */
        $scraper = self::getContainer()->get(Scraper::class);

        try {
            $actual = $scraper->scrapeMatches(...$subject);
        } catch (Throwable $e) {
            if ($expected instanceof Throwable) {
                self::assertInstanceOf(get_class($expected), $e);
                self::assertStringContainsString($expected->getMessage(), $e->getMessage());
            } else {
                throw $e;
            }
            return;
        }

        if ($expected instanceof Throwable) {
            self::fail('Expected exception to be thrown');
        }

        self::assertCount(count($expected), $actual);

        for ($i = 0; $i < count($expected); ++$i) {
            self::assertSame($expected[$i]['timestamp'], $actual[$i]['timestamp']);
            self::assertSame($expected[$i]['home_team_name'], $actual[$i]['home_team_name']);
            self::assertSame($expected[$i]['away_team_name'], $actual[$i]['away_team_name']);
            self::assertSame($expected[$i]['home_team_score'], $actual[$i]['home_team_score']);
            self::assertSame($expected[$i]['away_team_score'], $actual[$i]['away_team_score']);
            self::assertSame($expected[$i]['extra'], $actual[$i]['extra']);
        }

        $actual2 = $scraper->scrapeMatches(...$subject);

        self::assertCount(count($expected), $actual2);

        for ($i = 0; $i < count($expected); ++$i) {
            self::assertSame($expected[$i]['timestamp'], $actual2[$i]['timestamp']);
            self::assertSame($expected[$i]['home_team_name'], $actual2[$i]['home_team_name']);
            self::assertSame($expected[$i]['away_team_name'], $actual2[$i]['away_team_name']);
            self::assertSame($expected[$i]['home_team_score'], $actual[$i]['home_team_score']);
            self::assertSame($expected[$i]['away_team_score'], $actual[$i]['away_team_score']);
            self::assertSame($expected[$i]['extra'], $actual2[$i]['extra']);
        }
    }
}
