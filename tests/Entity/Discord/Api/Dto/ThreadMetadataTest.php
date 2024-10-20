<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ThreadMetadata;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ThreadMetadata
 */
final class ThreadMetadataTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ThreadMetadata $expected
     * @param ThreadMetadata $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->archived, $actual->archived);
        self::assertSame($expected->auto_archive_duration, $actual->auto_archive_duration);
        self::assertSame($expected->archive_timestamp, $actual->archive_timestamp);
        self::assertSame($expected->locked, $actual->locked);
        self::assertSame($expected->invitable, $actual->invitable);
        self::assertSame($expected->create_timestamp, $actual->create_timestamp);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"archived":true,"auto_archive_duration":60,"archive_timestamp":"test-archive-timestamp","locked":false%s}';

        return [
            [
                sprintf($subjectTemplate, ''),
                new ThreadMetadata(
                    archived: true,
                    auto_archive_duration: 60,
                    archive_timestamp: "test-archive-timestamp",
                    locked: false
                )
            ],
            [
                sprintf($subjectTemplate, ',"invitable":true,"create_timestamp":"test-create-timestamp"'),
                new ThreadMetadata(
                    archived: true,
                    auto_archive_duration: 60,
                    archive_timestamp: "test-archive-timestamp",
                    locked: false,
                    invitable: true,
                    create_timestamp: "test-create-timestamp"
                )
            ]
        ];
    }

    /**
     * @param string $subject
     * @param ThreadMetadata $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ThreadMetadata $expected): void
    {
        self::testDeserialization($subject, $expected, ThreadMetadata::class);
    }
}
