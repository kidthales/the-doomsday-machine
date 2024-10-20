<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ApplicationCommandData;
use App\Entity\Discord\Api\Enumeration\ApplicationCommandType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ApplicationCommandData
 */
final class ApplicationCommandDataTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ApplicationCommandData $expected
     * @param ApplicationCommandData $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->type, $actual->type);

        if (isset($expected->resolved)) {
            ResolvedDataTest::assertDeepSame($expected->resolved, $actual->resolved);
        } else {
            self::assertNull($actual->resolved);
        }

        if (isset($expected->options)) {
            self::assertSame(count($expected->options), count($actual->options));

            for ($i = 0; $i < count($expected->options); ++$i) {
                ApplicationCommandInteractionDataOptionTest::assertDeepSame($expected->options[$i], $actual->options[$i]);
            }
        } else {
            self::assertNull($actual->options);
        }

        self::assertSame($expected->guild_id, $actual->guild_id);
        self::assertSame($expected->target_id, $actual->target_id);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","name":"test-name","type":%s%s}';

        $optTemplates = [];
        $optsExpected = [];

        foreach (ApplicationCommandInteractionDataOptionTest::provider_deserialization() as [$optTemplate, $optExpected]) {
            $optTemplates[] = $optTemplate;
            $optsExpected[] = $optExpected;
        }

        $data = [];

        foreach (ApplicationCommandType::cases() as $type) {
            foreach (ResolvedDataTest::provider_deserialization() as [$resolvedTemplate, $resolvedExpected]) {
                $data[] = [
                    sprintf($subjectTemplate, $type->value, ',"resolved":' . $resolvedTemplate . ',"options":[' . implode(',', $optTemplates) . '],"guild_id":"test-guild-id","target_id":"test-target-id"'),
                    new ApplicationCommandData(
                        id: 'test-id',
                        name: 'test-name',
                        type: $type,
                        resolved: $resolvedExpected,
                        options: $optsExpected,
                        guild_id: 'test-guild-id',
                        target_id: 'test-target-id'
                    )
                ];
            }
        }

        return [
            [
                sprintf($subjectTemplate, ApplicationCommandType::CHAT_INPUT->value, ''),
                new ApplicationCommandData(
                    id: 'test-id',
                    name: 'test-name',
                    type: ApplicationCommandType::CHAT_INPUT
                )
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param ApplicationCommandData $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ApplicationCommandData $expected): void
    {
        self::testDeserialization($subject, $expected, ApplicationCommandData::class);
    }
}
