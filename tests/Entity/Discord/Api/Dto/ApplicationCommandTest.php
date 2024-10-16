<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\ApplicationCommand;
use App\Entity\Discord\Api\Enumeration\ApplicationCommandType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\ApplicationCommand
 */
final class ApplicationCommandTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param ApplicationCommand $expected
     * @param ApplicationCommand $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->description, $actual->description);
        self::assertSame($expected->default_member_permissions, $actual->default_member_permissions);
        self::assertSame($expected->version, $actual->version);
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->application_id, $actual->application_id);
        self::assertSame($expected->guild_id, $actual->guild_id);

        if (isset($expected->name_localizations)) {
            self::assertSame(count($expected->name_localizations), count($actual->name_localizations));

            foreach ($expected->name_localizations as $key => $value) {
                self::assertSame($expected->name_localizations[$key], $actual->name_localizations[$key]);
            }
        } else {
            self::assertNull($actual->name_localizations);
        }

        if (isset($expected->description_localizations)) {
            self::assertSame(count($expected->description_localizations), count($actual->description_localizations));

            foreach ($expected->description_localizations as $key => $value) {
                self::assertSame($expected->description_localizations[$key], $actual->description_localizations[$key]);
            }
        } else {
            self::assertNull($actual->description_localizations);
        }

        if (isset($expected->options)) {
            self::assertSame(count($expected->options), count($actual->options));

            for ($i = 0; $i < count($expected->options); ++$i) {
                ApplicationCommandOptionTest::assertDeepSame($expected->options[$i], $actual->options[$i]);
            }
        } else {
            self::assertNull($actual->options);
        }

        self::assertSame($expected->dm_permission, $actual->dm_permission);
        self::assertSame($expected->default_permission, $actual->default_permission);
        self::assertSame($expected->nsfw, $actual->nsfw);

        if (isset($expected->integration_types)) {
            self::assertSame(count($expected->integration_types), count($actual->integration_types));

            for ($i = 0; $i < count($expected->integration_types); ++$i) {
                self::assertSame($expected->integration_types[$i], $actual->integration_types[$i]);
            }
        } else {
            self::assertNull($actual->integration_types);
        }

        if (isset($expected->contexts)) {
            self::assertSame(count($expected->contexts), count($actual->contexts));

            for ($i = 0; $i < count($expected->contexts); ++$i) {
                self::assertSame($expected->contexts[$i], $actual->contexts[$i]);
            }
        } else {
            self::assertNull($actual->contexts);
        }

        self::assertSame($expected->handler, $actual->handler);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","name":"test-name","description":"test-description","default_member_permissions":null,"version":"test-version"%s}';

        $optTemplates = [];
        $optsExpected = [];

        foreach (ApplicationCommandOptionTest::provider_deserialization() as [$optTemplate, $optExpected]) {
            $optTemplates[] = $optTemplate;
            $optsExpected[] = $optExpected;
        }

        $data = [];

        foreach (ApplicationCommandType::cases() as $type) {
            $data[] = [
                sprintf($subjectTemplate, ',"type":' . $type->value . ',"options":[' . implode(',', $optTemplates) . ']'),
                new ApplicationCommand(
                    id: 'test-id',
                    name: 'test-name',
                    description: 'test-description',
                    default_member_permissions: null,
                    version: 'test-version',
                    type: $type,
                    options: $optsExpected
                )
            ];
        }

        return [
            [
                sprintf($subjectTemplate, ''),
                new ApplicationCommand(
                    id: 'test-id',
                    name: 'test-name',
                    description: 'test-description',
                    default_member_permissions: null,
                    version: 'test-version'
                )
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param ApplicationCommand $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, ApplicationCommand $expected): void
    {
        self::testDeserialization($subject, $expected, ApplicationCommand::class);
    }
}
