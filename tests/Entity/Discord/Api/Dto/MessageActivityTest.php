<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\MessageActivity;
use App\Entity\Discord\Api\Enumeration\MessageActivityType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\MessageActivity
 */
final class MessageActivityTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param MessageActivity $expected
     * @param MessageActivity $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->party_id, $actual->party_id);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"type":%s%s}';

        $data = [];

        foreach (MessageActivityType::cases() as $type) {
            $data[] = [
                sprintf($subjectTemplate, $type->value, ',"party_id":"test-party-id"'),
                new MessageActivity(type: $type, party_id: 'test-party-id')
            ];
        }

        return [
            [
                sprintf($subjectTemplate, MessageActivityType::JOIN->value, ''),
                new MessageActivity(type: MessageActivityType::JOIN)
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param MessageActivity $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, MessageActivity $expected): void
    {
        self::testDeserialization($subject, $expected, MessageActivity::class);
    }
}
