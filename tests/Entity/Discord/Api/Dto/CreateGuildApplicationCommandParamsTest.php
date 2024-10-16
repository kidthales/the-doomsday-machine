<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\CreateGlobalApplicationCommandParams;
use App\Entity\Discord\Api\Dto\CreateGuildApplicationCommandParams;
use App\Entity\Discord\Api\Enumeration\ApplicationCommandType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\CreateGuildApplicationCommandParams
 */
final class CreateGuildApplicationCommandParamsTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param CreateGuildApplicationCommandParams $expected
     * @param CreateGuildApplicationCommandParams $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->name, $actual->name);

        if (isset($expected->name_localizations)) {
            self::assertIsArray($actual->name_localizations);
            self::assertSame(count($expected->name_localizations), count($actual->name_localizations));

            foreach ($expected->name_localizations as $key => $value) {
                self::assertArrayHasKey($key, $actual->name_localizations);
                self::assertSame($value, $actual->name_localizations[$key]);
            }
        } else {
            self::assertNull($actual->name_localizations);
        }

        self::assertSame($expected->description, $actual->description);

        if (isset($expected->description_localizations)) {
            self::assertIsArray($actual->description_localizations);
            self::assertSame(count($expected->description_localizations), count($actual->description_localizations));

            foreach ($expected->description_localizations as $key => $value) {
                self::assertArrayHasKey($key, $actual->description_localizations);
                self::assertSame($value, $actual->description_localizations[$key]);
            }
        } else {
            self::assertNull($actual->description_localizations);
        }

        if (isset($expected->options)) {
            self::assertIsArray($actual->options);
            self::assertSame(count($expected->options), count($actual->options));

            for ($i = 0; $i < count($expected->options); ++$i) {
                ApplicationCommandOptionTest::assertDeepSame($expected->options[$i], $actual->options[$i]);
            }
        } else {
            self::assertNull($actual->options);
        }

        self::assertSame($expected->default_member_permissions, $actual->default_member_permissions);
        self::assertSame($expected->default_permission, $actual->default_permission);
        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->nsfw, $actual->nsfw);
    }

    /**
     * @return array
     */
    public static function provider_fromCreateGlobalApplicationCommandParams(): array
    {
        return array_map(
            fn (array $dataset) => [$dataset[0]],
            CreateGlobalApplicationCommandParamsTest::provider_serialization()
        );
    }

    /**
     * @param CreateGlobalApplicationCommandParams $globalParams
     * @return void
     * @dataProvider provider_fromCreateGlobalApplicationCommandParams
     */
    public function test_fromCreateGlobalApplicationCommandEndpointParams(
        CreateGlobalApplicationCommandParams $globalParams
    ): void
    {
        $guildParams = CreateGuildApplicationCommandParams::fromCreateGlobalApplicationCommandParams($globalParams);

        self::assertEquals($globalParams->name, $guildParams->name);
        self::assertEquals($globalParams->name_localizations, $guildParams->name_localizations);
        self::assertEquals($globalParams->description, $guildParams->description);
        self::assertEquals($globalParams->description_localizations, $guildParams->description_localizations);
        self::assertEquals($globalParams->options, $guildParams->options);
        self::assertEquals($globalParams->default_member_permissions, $guildParams->default_member_permissions);
        self::assertEquals($globalParams->default_permission, $guildParams->default_permission);
        self::assertEquals($globalParams->type, $guildParams->type);
        self::assertEquals($globalParams->nsfw, $guildParams->nsfw);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"name":"test-name"%s}';

        $baseExpected = new CreateGuildApplicationCommandParams(name: 'test-name');

        $withOptions = clone $baseExpected;
        $withOptions->options = [];
        $withOptionsSubject = [];

        foreach (ApplicationCommandOptionTest::provider_deserialization() as $dataset) {
            $withOptionsSubject[] = $dataset[0];
            $withOptions->options[] = $dataset[1];
        }

        return [
            [sprintf($subjectTemplate, ''), $baseExpected],
            [sprintf($subjectTemplate, sprintf(',"options":[%s]', implode(',', $withOptionsSubject))), $withOptions]
        ];
    }

    /**
     * @param string $subject
     * @param CreateGuildApplicationCommandParams $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, CreateGuildApplicationCommandParams $expected): void
    {
        self::testDeserialization($subject, $expected, CreateGuildApplicationCommandParams::class);
    }

    /**
     * @return array
     */
    public static function provider_serialization(): array
    {
        $baseSubject = new CreateGuildApplicationCommandParams(name: 'test-name');

        $expectedTemplate = '{"name":"test-name"%s}';

        $withNullNameLocalizations = clone $baseSubject;
        $withNullNameLocalizations->normalizeNullNameLocalizations = true;

        $withNameLocalizations = clone $baseSubject;
        $withNameLocalizations->name_localizations = ['test-locale-key' => 'test-locale-value'];

        $withDescription = clone $baseSubject;
        $withDescription->description = 'test-description';

        $withNullDescriptionLocalizations = clone $baseSubject;
        $withNullDescriptionLocalizations->normalizeNullDescriptionLocalizations = true;

        $withDescriptionLocalizations = clone $baseSubject;
        $withDescriptionLocalizations->description_localizations = ['test-locale-key' => 'test-locale-value'];

        $withEmptyOptions = clone $baseSubject;
        $withEmptyOptions->options = [];

        $withOptions = clone $baseSubject;
        $withOptions->options = [];
        $withOptionsExpected = [];

        foreach (ApplicationCommandOptionTest::provider_serialization() as $dataset) {
            $withOptions->options[] = $dataset[0];
            $withOptionsExpected[] = $dataset[1];
        }

        $withNullDefaultMemberPermissions = clone $baseSubject;
        $withNullDefaultMemberPermissions->normalizeNullDefaultMemberPermissions = true;

        $withDefaultMemberPermissions = clone $baseSubject;
        $withDefaultMemberPermissions->default_member_permissions = 'test-default-member-permissions';

        $withDefaultPermission = clone $baseSubject;
        $withDefaultPermission->default_permission = false;

        $withType = clone $baseSubject;
        $withType->type = ApplicationCommandType::CHAT_INPUT;

        $withNsfw = clone $baseSubject;
        $withNsfw->nsfw = true;

        return [
            [$baseSubject, sprintf($expectedTemplate, '')],
            [$withNullNameLocalizations, sprintf($expectedTemplate, ',"name_localizations":null')],
            [
                $withNameLocalizations,
                sprintf($expectedTemplate, ',"name_localizations":{"test-locale-key":"test-locale-value"}')
            ],
            [$withDescription, sprintf($expectedTemplate, ',"description":"test-description"')],
            [$withNullDescriptionLocalizations, sprintf($expectedTemplate, ',"description_localizations":null')],
            [
                $withDescriptionLocalizations,
                sprintf($expectedTemplate, ',"description_localizations":{"test-locale-key":"test-locale-value"}')
            ],
            [$withEmptyOptions, sprintf($expectedTemplate, ',"options":[]')],
            [
                $withOptions,
                sprintf($expectedTemplate, sprintf(',"options":[%s]', implode(',', $withOptionsExpected)))
            ],
            [$withNullDefaultMemberPermissions, sprintf($expectedTemplate, ',"default_member_permissions":null')],
            [
                $withDefaultMemberPermissions,
                sprintf($expectedTemplate, ',"default_member_permissions":"test-default-member-permissions"')
            ],
            [$withDefaultPermission, sprintf($expectedTemplate, ',"default_permission":false')],
            [$withType, sprintf($expectedTemplate, ',"type":1')],
            [$withNsfw, sprintf($expectedTemplate, ',"nsfw":true')]
        ];
    }

    /**
     * @param CreateGuildApplicationCommandParams $subject
     * @param string $expected
     * @return void
     * @dataProvider provider_serialization
     */
    public function test_serialization(CreateGuildApplicationCommandParams $subject, string $expected): void
    {
        self::testSerialization($subject, $expected);
    }
}
