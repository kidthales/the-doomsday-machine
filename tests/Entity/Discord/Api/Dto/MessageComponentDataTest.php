<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\MessageComponentData;
use App\Entity\Discord\Api\Enumeration\ComponentType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\MessageComponentData
 */
final class MessageComponentDataTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param MessageComponentData $expected
     * @param MessageComponentData $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->custom_id, $actual->custom_id);
        self::assertSame($expected->component_type, $actual->component_type);

        if (isset($expected->values)) {
            self::assertSame(count($expected->values), count($actual->values));

            for ($i = 0; $i < count($expected->values); ++$i) {
                self::assertSame($expected->values[$i], $actual->values[$i]);
            }
        } else {
            self::assertNull($actual->values);
        }

        if (isset($expected->resolved)) {
            ResolvedDataTest::assertDeepSame($expected->resolved, $actual->resolved);
        } else {
            self::assertNull($actual->resolved);
        }
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"custom_id":"test-id","component_type":%s%s}';

        $data = [];

        foreach ([ComponentType::Button, ComponentType::StringSelect, ComponentType::UserSelect, ComponentType::RoleSelect, ComponentType::MentionableSelect, ComponentType::ChannelSelect] as $componentType) {
            foreach (ResolvedDataTest::provider_deserialization() as [$resolvedTemplate, $resolvedExpected]) {
                $data[] = [
                    sprintf($subjectTemplate, $componentType->value, ',"values":["test-1","test-2"],"resolved":' . $resolvedTemplate),
                    new MessageComponentData(
                        custom_id: 'test-id',
                        component_type: $componentType,
                        values: ['test-1','test-2'],
                        resolved: $resolvedExpected
                    )
                ];
            }
        }

        return [
            [
                sprintf($subjectTemplate, 'null', ''),
                new MessageComponentData(custom_id: 'test-id', component_type: null)
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param MessageComponentData $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, MessageComponentData $expected): void
    {
        self::testDeserialization($subject, $expected, MessageComponentData::class);
    }
}
