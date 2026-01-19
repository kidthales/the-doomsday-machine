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

namespace App\Scraper;

use App\Entity\FootyStats\EndpointPayload;
use App\Entity\FootyStats\Target;
use App\Filesystem\FileDepot;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\String\s;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
final class FootyStatsScraper
{
    private static function makePath(string $nation, string $competition, string $season, string $context): string
    {
        return sprintf(
            'footy_stats/scraper/%s_%s_%s_%s.html',
            s($nation)->snake()->toString(),
            s($competition)->snake()->toString(),
            s($season)->snake()->toString(),
            s($context)->snake()->toString()
        );
    }

    /**
     * @param string $content
     * @return array{
     *     current: string,
     *     previous: array<string, EndpointPayload>
     * }
     */
    private static function doAvailableSeasonsScrape(string $content): array
    {
        $seasonsCrawler = (new Crawler($content))->filter('#teamSummary .season .drop-down-parent');

        $previousSeasons = [];
        $data = $seasonsCrawler->filter('.changeLeagueDataButton')->each(function (Crawler $node) use (&$previousSeasons) {
            $previousSeasons[] = s($node->innerText())->trim()->toString();
            return EndpointPayload::fromNode($node);
        });

        return [
            'current' => s($seasonsCrawler->innerText())->trim()->toString(),
            'previous' => array_combine($previousSeasons, $data),
        ];
    }

    /**
     * @var array{
     *      endpoint: string,
     *      current_season_ttl: int,
     *      nation_competitions: array<string, array<string, array{
     *          overview_path: string,
     *          fixtures_path: string
     *      }>>
     *  }
     */
    private readonly array $config;

    /**
     * @var array<string, array<string, array{
     *     current: string,
     *     previous: array{
     *         overview: array<string, EndpointPayload>,
     *         fixtures: array<string, EndpointPayload>
     *     }
     * }>>
     */
    private array $availableSeasons = [];

    /**
     * @param array $config
     * @param FileDepot $fileDepot
     * @param HttpClientInterface $footyStatsClient
     */
    public function __construct(
        #[Autowire(param: 'app.footy_stats.scraper')] array $config,
        private readonly FileDepot                          $fileDepot,
        private readonly HttpClientInterface                $footyStatsClient
    )
    {
        $this->config = $config;
    }

    /**
     * Return all supported nations.
     *
     * @return string[]
     */
    public function getNations(): array
    {
        return array_keys($this->config['nation_competitions']);
    }

    /**
     * Return all supported competitions for the specified nation.
     *
     * @param string $nation
     * @return string[]
     */
    public function getCompetitions(string $nation): array
    {
        return array_keys($this->config['nation_competitions'][$nation] ?? []);
    }

    /**
     * @param string $nation
     * @param string $competition
     * @return array{
     *     current: string,
     *     previous: array{
     *         overview: array<string, EndpointPayload>,
     *         fixtures: array<string, EndpointPayload>
     *     }
     *  }
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function scrapeAvailableSeasons(string $nation, string $competition): array
    {
        $this->validateNationCompetition($nation, $competition);

        if (isset($this->availableSeasons[$nation][$competition])) {
            return $this->availableSeasons[$nation][$competition];
        }

        $content = $this->fetchCurrentSeasonContent($nation, $competition);

        $overviewAvailableSeasons = self::doAvailableSeasonsScrape($content['overview']);
        $fixturesAvailableSeasons = self::doAvailableSeasonsScrape($content['fixtures']);

        if ($overviewAvailableSeasons['current'] !== $fixturesAvailableSeasons['current']) {
            throw new RuntimeException(
                sprintf(
                    'Overview current season (%s) does not match fixtures current season (%s)',
                    $overviewAvailableSeasons['current'],
                    $fixturesAvailableSeasons['current']
                )
            );
        }

        $overviewPreviousSeasons = array_keys($overviewAvailableSeasons['previous']);
        $fixturesPreviousSeasons = array_keys($fixturesAvailableSeasons['previous']);

        if (count($overviewPreviousSeasons) !== count($fixturesPreviousSeasons)) {
            throw new RuntimeException(
                sprintf(
                    'Overview previous seasons count (%d) does not match fixtures previous seasons count (%d)',
                    count($overviewPreviousSeasons),
                    count($fixturesPreviousSeasons)
                )
            );
        }

        sort($overviewPreviousSeasons);
        sort($fixturesPreviousSeasons);

        for ($i = 0; $i < count($overviewPreviousSeasons); ++$i) {
            if ($overviewPreviousSeasons[$i] !== $fixturesPreviousSeasons[$i]) {
                throw new RuntimeException(
                    sprintf(
                        'Overview previous season (%s) does not match fixtures previous season (%s)',
                        $overviewPreviousSeasons[$i],
                        $fixturesPreviousSeasons[$i]
                    )
                );
            }
        }

        $availableSeasons = [
            'current' => $overviewAvailableSeasons['current'],
            'previous' => [
                'overview' => $overviewAvailableSeasons['previous'],
                'fixtures' => $fixturesAvailableSeasons['previous'],
            ]
        ];

        if (!isset($this->availableSeasons[$nation])) {
            $this->availableSeasons[$nation] = [];
        }

        return $this->availableSeasons[$nation][$competition] = $availableSeasons;
    }

    /**
     * @param Target $target
     * @return string[][]
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function scrapeTeamNames(Target $target): array
    {
        $this->validateNationCompetition($target->nation, $target->competition);

        $content = $this->fetchContent($target);

        $overviewTeamNames = (new Crawler($content['overview']))
            ->filter('tbody')
            ->first()
            ->filter('tr')
            ->each(function (Crawler $node) {
                $teamNode = $node->children()->slice(2, 1)->children()->first();
                return [s($teamNode->text())->trim()->toString(), $teamNode->attr('href')];
            });

        $fixturesTeamNames = (new Crawler($content['fixtures']))
            ->filter('.leagueTable td.leagueTableTeamName a')
            ->each(fn(Crawler $node) => [s($node->text())->trim()->toString(), $node->attr('href')]);

        if (count($overviewTeamNames) !== count($fixturesTeamNames)) {
            throw new RuntimeException(
                sprintf(
                    'Overview team name count (%d) does not match fixtures team name count (%d)',
                    count($overviewTeamNames),
                    count($fixturesTeamNames)
                )
            );
        }

        usort($overviewTeamNames, fn(array $a, array $b) => strcmp($a[1], $b[1]));
        usort($fixturesTeamNames, fn(array $a, array $b) => strcmp($a[1], $b[1]));

        for ($i = 0; $i < count($overviewTeamNames); ++$i) {
            if ($overviewTeamNames[$i][1] !== $fixturesTeamNames[$i][1]) {
                throw new RuntimeException(
                    sprintf(
                        'Overview team name href (%s) does not match fixtures team name href (%s)',
                        $overviewTeamNames[$i][1],
                        $fixturesTeamNames[$i][1]
                    )
                );
            }
        }

        return array_map(null, array_column($overviewTeamNames, 0), array_column($fixturesTeamNames, 0));
    }

    /**
     * @param Target $target
     * @return array<array{
     *     timestamp: int,
     *     home_team_name: string,
     *     away_team_name: string,
     *     home_team_score: int|null,
     *     away_team_score: int|null,
     *     extra: mixed
     * }>
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function scrapeMatches(Target $target): array
    {
        $this->validateNationCompetition($target->nation, $target->competition);

        $content = $this->fetchContent($target)['fixtures'];

        $matchRowsCrawler = (new Crawler($content))->filter('#matches-list ul.match.row');

        return $matchRowsCrawler->each(function (Crawler $node) {
            $scoreCrawler = $node->filter('.h2h-link span');

            if ($scoreCrawler->count() === 2 && s($scoreCrawler->last()->text())->trim()->toString() === 'FT') {
                list ($homeTeamScore, $awayTeamScore) = array_map(
                    fn($s) => intval($s),
                    explode(' - ', s($scoreCrawler->first()->text())->trim()->toString())
                );
            } else {
                $homeTeamScore = $awayTeamScore = null;
            }

            return [
                'timestamp' => intval($node->filter('[data-time]')->first()->attr('data-time')),
                'home_team_name' => s($node->filter('.home span')->first()->text())->trim()->toString(),
                'away_team_name' => s($node->filter('.away span')->first()->text())->trim()->toString(),
                'home_team_score' => $homeTeamScore,
                'away_team_score' => $awayTeamScore,
                'extra' => null
            ];
        });
    }

    /**
     * @param string $nation
     * @param string $competition
     * @return void
     * @throws RuntimeException
     */
    private function validateNationCompetition(string $nation, string $competition): void
    {
        if (!array_key_exists($nation, $this->config['nation_competitions'])) {
            throw new RuntimeException(sprintf('Unsupported nation: %s', $nation));
        }

        if (!array_key_exists($competition, $this->config['nation_competitions'][$nation])) {
            throw new RuntimeException(sprintf('Unsupported competition: %s', $competition));
        }
    }

    /**
     * @param Target $target
     * @return array{
     *      overview: string,
     *      fixtures: string
     *  }
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function fetchContent(Target $target): array
    {
        $availableSeasons = $this->scrapeAvailableSeasons($target->nation, $target->competition);

        if ($target->season === $availableSeasons['current']) {
            return $this->fetchCurrentSeasonContent($target->nation, $target->competition);
        } else if (
            in_array($target->season, array_keys($availableSeasons['previous']['overview'])) &&
            in_array($target->season, array_keys($availableSeasons['previous']['fixtures']))
        ) {
            return $this->fetchPreviousSeasonContent(
                $target->nation,
                $target->competition,
                $availableSeasons['previous']['overview'][$target->season],
                $availableSeasons['previous']['fixtures'][$target->season]
            );
        }

        throw new RuntimeException(sprintf('Unsupported season: %s', $target->season));
    }

    /**
     * @param string $nation
     * @param string $competition
     * @return array{
     *     overview: string,
     *     fixtures: string
     * }
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function fetchCurrentSeasonContent(string $nation, string $competition): array
    {
        $overviewPath = self::makePath($nation, $competition, 'current', 'overview');
        $fixturesPath = self::makePath($nation, $competition, 'current', 'fixtures');

        $overviewModifiedTime = $this->fileDepot->filemtime($overviewPath);
        $fixturesModifiedTime = $this->fileDepot->filemtime($fixturesPath);

        if (
            $overviewModifiedTime === false ||
            $fixturesModifiedTime === false ||
            time() - $overviewModifiedTime > $this->config['current_season_ttl'] ||
            time() - $fixturesModifiedTime > $this->config['current_season_ttl']
        ) {
            $this->fileDepot->remove([$overviewPath, $fixturesPath]);

            $config = $this->config['nation_competitions'][$nation][$competition];

            $overviewContent = $this->requestCurrentSeasonContent($config['overview_path']);
            $fixturesContent = $this->requestCurrentSeasonContent($config['fixtures_path']);

            $this->fileDepot->appendToFile($overviewPath, $overviewContent);
            $this->fileDepot->appendToFile($fixturesPath, $fixturesContent);
        } else {
            $overviewContent = $this->fileDepot->readFile($overviewPath);
            $fixturesContent = $this->fileDepot->readFile($fixturesPath);
        }

        return ['overview' => $overviewContent, 'fixtures' => $fixturesContent];
    }

    /**
     * @param string $nation
     * @param string $competition
     * @param EndpointPayload $overviewPayload
     * @param EndpointPayload $fixturesPayload
     * @return array{
     *      overview: string,
     *      fixtures: string
     *  }
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function fetchPreviousSeasonContent(
        string          $nation,
        string          $competition,
        EndpointPayload $overviewPayload,
        EndpointPayload $fixturesPayload
    ): array
    {
        $overviewPath = self::makePath($nation, $competition, $overviewPayload->hash, $overviewPayload->zzz);
        $fixturesPath = self::makePath($nation, $competition, $fixturesPayload->hash, $fixturesPayload->zzz);

        if (!$this->fileDepot->exists($overviewPath) || !$this->fileDepot->exists($fixturesPath)) {
            $this->fileDepot->remove([$overviewPath, $fixturesPath]);

            $overviewContent = $this->requestPreviousSeasonContent($overviewPayload);
            $fixturesContent = $this->requestPreviousSeasonContent($fixturesPayload);

            $this->fileDepot->appendToFile($overviewPath, $overviewContent);
            $this->fileDepot->appendToFile($fixturesPath, $fixturesContent);
        } else {
            $overviewContent = $this->fileDepot->readFile($overviewPath);
            $fixturesContent = $this->fileDepot->readFile($fixturesPath);
        }

        return ['overview' => $overviewContent, 'fixtures' => $fixturesContent];
    }

    /**
     * @param string $path
     * @return string
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function requestCurrentSeasonContent(string $path): string
    {
        return $this->footyStatsClient->request('GET', $path)->getContent();
    }

    /**
     * @param EndpointPayload $payload
     * @return string
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function requestPreviousSeasonContent(EndpointPayload $payload): string
    {
        return $this->footyStatsClient->request(
            'POST',
            $this->config['endpoint'],
            ['body' => EndpointPayload::toRequestBody($payload)]
        )->getContent();
    }
}
