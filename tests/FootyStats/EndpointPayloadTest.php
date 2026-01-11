<?php

declare(strict_types=1);

namespace App\Tests\FootyStats;

use App\FootyStats\EndpointPayload;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

final class EndpointPayloadTest extends TestCase
{
    public static function provide_test_fromNode(): array
    {
        return [
            'no attributes' => [
                (new Crawler('<span></span>'))->filter('span')->first(),
                new EndpointPayload()
            ],
            'hash' => [
                (new Crawler('<span data-hash="test-hash"></span>'))->filter('span')->first(),
                new EndpointPayload(hash: 'test-hash')
            ],
            'z' => [
                (new Crawler('<span data-z="test-z"></span>'))->filter('span')->first(),
                new EndpointPayload(z: 'test-z')
            ],
            'zzz' => [
                (new Crawler('<span data-zzz="test-zzz"></span>'))->filter('span')->first(),
                new EndpointPayload(zzz: 'test-zzz')
            ],
            'zzzz' => [
                (new Crawler('<span data-zzzz="test-zzzz"></span>'))->filter('span')->first(),
                new EndpointPayload(zzzz: 'test-zzzz')
            ],
            'hash, z' => [
                (new Crawler('<span data-hash="test-hash" data-z="test-z"></span>'))->filter('span')->first(),
                new EndpointPayload(hash: 'test-hash', z: 'test-z')
            ],
            'hash, zzz' => [
                (new Crawler('<span data-hash="test-hash" data-zzz="test-zzz"></span>'))->filter('span')->first(),
                new EndpointPayload(hash: 'test-hash', zzz: 'test-zzz')
            ],
            'hash, zzzz' => [
                (new Crawler('<span data-hash="test-hash" data-zzzz="test-zzzz"></span>'))->filter('span')->first(),
                new EndpointPayload(hash: 'test-hash', zzzz: 'test-zzzz')
            ],
            'z, zzz' => [
                (new Crawler('<span data-z="test-z" data-zzz="test-zzz"></span>'))->filter('span')->first(),
                new EndpointPayload(z: 'test-z', zzz: 'test-zzz')
            ],
            'z, zzzz' => [
                (new Crawler('<span data-z="test-z" data-zzzz="test-zzzz"></span>'))->filter('span')->first(),
                new EndpointPayload(z: 'test-z', zzzz: 'test-zzzz')
            ],
            'zzz, zzzz' => [
                (new Crawler('<span data-zzz="test-zzz" data-zzzz="test-zzzz"></span>'))->filter('span')->first(),
                new EndpointPayload(zzz: 'test-zzz', zzzz: 'test-zzzz')
            ],
            'hash, z, zzz' => [
                (new Crawler('<span data-hash="test-hash" data-z="test-z" data-zzz="test-zzz"></span>'))->filter('span')->first(),
                new EndpointPayload(hash: 'test-hash', z: 'test-z', zzz: 'test-zzz')
            ],
            'hash, z, zzzz' => [
                (new Crawler('<span data-hash="test-hash" data-z="test-z" data-zzzz="test-zzzz"></span>'))->filter('span')->first(),
                new EndpointPayload(hash: 'test-hash', z: 'test-z', zzzz: 'test-zzzz')
            ],
            'hash, zzz, zzzz' => [
                (new Crawler('<span data-hash="test-hash" data-zzz="test-zzz" data-zzzz="test-zzzz"></span>'))->filter('span')->first(),
                new EndpointPayload(hash: 'test-hash', zzz: 'test-zzz', zzzz: 'test-zzzz')
            ],
            'z, zzz, zzzz' => [
                (new Crawler('<span data-z="test-z" data-zzz="test-zzz" data-zzzz="test-zzzz"></span>'))->filter('span')->first(),
                new EndpointPayload(z: 'test-z', zzz: 'test-zzz', zzzz: 'test-zzzz')
            ],
            'hash, z, zzz, zzzz' => [
                (new Crawler('<span data-hash="test-hash" data-z="test-z" data-zzz="test-zzz" data-zzzz="test-zzzz"></span>'))->filter('span')->first(),
                new EndpointPayload(hash: 'test-hash', z: 'test-z', zzz: 'test-zzz', zzzz: 'test-zzzz')
            ]
        ];
    }

    #[DataProvider('provide_test_fromNode')]
    public function test_fromNode(Crawler $subject, EndpointPayload $expected): void
    {
        $actual = EndpointPayload::fromNode($subject);

        self::assertSame($expected->hash, $actual->hash);
        self::assertSame($expected->z, $actual->z);
        self::assertSame($expected->zzz, $actual->zzz);
        self::assertSame($expected->zzzz, $actual->zzzz);
    }

    public static function provide_test_toRequestBody(): array
    {
        return [
            'no fields' => [
                new EndpointPayload(),
                ['hash' => null, 'cur' => null, 'zzz' => null, 'zzzz' => null]
            ],
            'hash' => [
                new EndpointPayload(hash: 'test-hash'),
                ['hash' => 'test-hash', 'cur' => null, 'zzz' => null, 'zzzz' => null]
            ],
            'cur' => [
                new EndpointPayload(z: 'test-z'),
                ['hash' => null, 'cur' => 'test-z', 'zzz' => null, 'zzzz' => null]
            ],
            'zzz' => [
                new EndpointPayload(zzz: 'test-zzz'),
                ['hash' => null, 'cur' => null, 'zzz' => 'test-zzz', 'zzzz' => null]
            ],
            'zzzz' => [
                new EndpointPayload(zzzz: 'test-zzzz'),
                ['hash' => null, 'cur' => null, 'zzz' => null, 'zzzz' => 'test-zzzz']
            ],
            'hash, cur' => [
                new EndpointPayload(hash: 'test-hash', z: 'test-z'),
                ['hash' => 'test-hash', 'cur' => 'test-z', 'zzz' => null, 'zzzz' => null]
            ],
            'hash, zzz' => [
                new EndpointPayload(hash: 'test-hash', zzz: 'test-zzz'),
                ['hash' => 'test-hash', 'cur' => null, 'zzz' => 'test-zzz', 'zzzz' => null]
            ],
            'hash, zzzz' => [
                new EndpointPayload(hash: 'test-hash', zzzz: 'test-zzzz'),
                ['hash' => 'test-hash', 'cur' => null, 'zzz' => null, 'zzzz' => 'test-zzzz']
            ],
            'cur, zzz' => [
                new EndpointPayload(z: 'test-z', zzz: 'test-zzz'),
                ['hash' => null, 'cur' => 'test-z', 'zzz' => 'test-zzz', 'zzzz' => null]
            ],
            'cur, zzzz' => [
                new EndpointPayload(z: 'test-z', zzzz: 'test-zzzz'),
                ['hash' => null, 'cur' => 'test-z', 'zzz' => null, 'zzzz' => 'test-zzzz']
            ],
            'zzz, zzzz' => [
                new EndpointPayload(zzz: 'test-zzz', zzzz: 'test-zzzz'),
                ['hash' => null, 'cur' => null, 'zzz' => 'test-zzz', 'zzzz' => 'test-zzzz']
            ],
            'hash, cur, zzz' => [
                new EndpointPayload(hash: 'test-hash', z: 'test-z', zzz: 'test-zzz'),
                ['hash' => 'test-hash', 'cur' => 'test-z', 'zzz' => 'test-zzz', 'zzzz' => null]
            ],
            'hash, cur, zzzz' => [
                new EndpointPayload(hash: 'test-hash', z: 'test-z', zzzz: 'test-zzzz'),
                ['hash' => 'test-hash', 'cur' => 'test-z', 'zzz' => null, 'zzzz' => 'test-zzzz']
            ],
            'hash, zzz, zzzz' => [
                new EndpointPayload(hash: 'test-hash', zzz: 'test-zzz', zzzz: 'test-zzzz'),
                ['hash' => 'test-hash', 'cur' => null, 'zzz' => 'test-zzz', 'zzzz' => 'test-zzzz']
            ],
            'cur, zzz, zzzz' => [
                new EndpointPayload(z: 'test-z', zzz: 'test-zzz', zzzz: 'test-zzzz'),
                ['hash' => null, 'cur' => 'test-z', 'zzz' => 'test-zzz', 'zzzz' => 'test-zzzz']
            ],
            'hash, cur, zzz, zzzz' => [
                new EndpointPayload(hash: 'test-hash', z: 'test-z', zzz: 'test-zzz', zzzz: 'test-zzzz'),
                ['hash' => 'test-hash', 'cur' => 'test-z', 'zzz' => 'test-zzz', 'zzzz' => 'test-zzzz']
            ]
        ];
    }

    #[DataProvider('provide_test_toRequestBody')]
    public function test_toRequestBody(EndpointPayload $subject, array $expected): void
    {
        $actual = EndpointPayload::toRequestBody($subject);

        self::assertSame($expected['hash'], $actual['hash']);
        self::assertSame($expected['cur'], $actual['cur']);
        self::assertSame($expected['zzz'], $actual['zzz']);
        self::assertSame($expected['zzzz'], $actual['zzzz']);
    }
}
