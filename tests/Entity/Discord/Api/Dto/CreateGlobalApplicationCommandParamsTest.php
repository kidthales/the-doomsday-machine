<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\CreateGlobalApplicationCommandParams;
use App\Entity\Discord\Api\Enumeration\ApplicationCommandType;
use App\Entity\Discord\Api\Enumeration\ApplicationIntegrationType;
use App\Entity\Discord\Api\Enumeration\InteractionContextType;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\CreateGlobalApplicationCommandParams
 */
final class CreateGlobalApplicationCommandParamsTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param CreateGlobalApplicationCommandParams $expected
     * @param CreateGlobalApplicationCommandParams $actual
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
        self::assertSame($expected->dm_permission, $actual->dm_permission);
        self::assertSame($expected->default_permission, $actual->default_permission);

        if (isset($expected->integration_types)) {
            self::assertIsArray($actual->integration_types);
            self::assertSame(count($expected->integration_types), count($actual->integration_types));

            for ($i = 0; $i < count($expected->integration_types); ++$i) {
                self::assertSame($expected->integration_types[$i], $actual->integration_types[$i]);
            }
        } else {
            self::assertNull($actual->integration_types);
        }

        if (isset($expected->contexts)) {
            self::assertIsArray($actual->contexts);
            self::assertSame(count($expected->contexts), count($actual->contexts));

            for ($i = 0; $i < count($expected->contexts); ++$i) {
                self::assertSame($expected->contexts[$i], $actual->contexts[$i]);
            }
        } else {
            self::assertNull($actual->contexts);
        }

        self::assertSame($expected->type, $actual->type);
        self::assertSame($expected->nsfw, $actual->nsfw);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"name":"test-name"%s}';

        $baseExpected = new CreateGlobalApplicationCommandParams(name: 'test-name');

        $withNameLocalizations = clone $baseExpected;
        $withNameLocalizations->name_localizations = ['test-locale-key' => 'test-locale-value'];

        $withIntegrationTypes = clone $baseExpected;
        $withIntegrationTypes->integration_types = ApplicationIntegrationType::cases();

        return [
            [sprintf($subjectTemplate, ''), $baseExpected],
            [
                sprintf($subjectTemplate, ',"name_localizations":{"test-locale-key":"test-locale-value"}'),
                $withNameLocalizations
            ],
            [sprintf($subjectTemplate, ',"integration_types":[0,1]'), $withIntegrationTypes],
        ];
    }

    /**
     * @param string $subject
     * @param CreateGlobalApplicationCommandParams $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, CreateGlobalApplicationCommandParams $expected): void
    {
        self::testDeserialization($subject, $expected, CreateGlobalApplicationCommandParams::class);
    }

    /**
     * @return array
     */
    public static function provider_serialization(): array
    {
        $baseSubject = new CreateGlobalApplicationCommandParams(name: 'test-name');

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

        $withNullDmPermission = clone $baseSubject;
        $withNullDmPermission->normalizeNullDmPermission = true;

        $withDmPermission = clone $baseSubject;
        $withDmPermission->dm_permission = true;

        $withDefaultPermission = clone $baseSubject;
        $withDefaultPermission->default_permission = false;

        $withIntegrationTypes = clone $baseSubject;
        $withIntegrationTypes->integration_types = ApplicationIntegrationType::cases();

        $withContexts = clone $baseSubject;
        $withContexts->contexts = InteractionContextType::cases();

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
            [$withNullDmPermission, sprintf($expectedTemplate, ',"dm_permission":null')],
            [$withDmPermission, sprintf($expectedTemplate, ',"dm_permission":true')],
            [$withDefaultPermission, sprintf($expectedTemplate, ',"default_permission":false')],
            [$withIntegrationTypes, sprintf($expectedTemplate, ',"integration_types":[0,1]')],
            [$withContexts, sprintf($expectedTemplate, ',"contexts":[0,1,2]')],
            [$withType, sprintf($expectedTemplate, ',"type":1')],
            [$withNsfw, sprintf($expectedTemplate, ',"nsfw":true')]
        ];
    }

    /**
     * @param CreateGlobalApplicationCommandParams $subject
     * @param string $expected
     * @return void
     * @dataProvider provider_serialization
     */
    public function test_serialization(CreateGlobalApplicationCommandParams $subject, string $expected): void
    {
        self::testSerialization($subject, $expected);
    }
}
