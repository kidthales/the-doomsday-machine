<?php

namespace App\Tests\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Dto\Application;
use App\Tests\TestHelper\AbstractSerializableSubjectTestCase;

/**
 * @covers \App\Entity\Discord\Api\Dto\Application
 */
final class ApplicationTest extends AbstractSerializableSubjectTestCase
{
    /**
     * @param Application $expected
     * @param Application $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        self::assertSame($expected->id, $actual->id);
        self::assertSame($expected->name, $actual->name);
        self::assertSame($expected->icon, $actual->icon);
        self::assertSame($expected->description, $actual->description);
        self::assertSame($expected->bot_public, $actual->bot_public);
        self::assertSame($expected->bot_require_code_grant, $actual->bot_require_code_grant);
        self::assertSame($expected->verify_key, $actual->verify_key);

        if (isset($expected->team)) {
            TeamTest::assertDeepSame($expected->team, $actual->team);
        } else {
            self::assertNull($actual->team);
        }

        if (isset($expected->rpc_origins)) {
            self::assertSame(count($expected->rpc_origins), count($actual->rpc_origins));

            for ($i = 0; $i < count($expected->rpc_origins); ++$i) {
                self::assertSame($expected->rpc_origins[$i], $actual->rpc_origins[$i]);
            }
        } else {
            self::assertNull($actual->rpc_origins);
        }

        if (isset($expected->bot)) {
            UserTest::assertDeepSame($expected->bot, $actual->bot);
        } else {
            self::assertNull($actual->bot);
        }

        self::assertSame($expected->terms_of_service_url, $actual->terms_of_service_url);
        self::assertSame($expected->privacy_policy_url, $actual->privacy_policy_url);

        if (isset($expected->owner)) {
            UserTest::assertDeepSame($expected->owner, $actual->owner);
        } else {
            self::assertNull($actual->owner);
        }

        self::assertSame($expected->guild_id, $actual->guild_id);

        if (isset($expected->guild)) {
            GuildTest::assertDeepSame($expected->guild, $actual->guild);
        } else {
            self::assertNull($actual->guild);
        }

        self::assertSame($expected->primary_sku_id, $actual->primary_sku_id);
        self::assertSame($expected->slug, $actual->slug);
        self::assertSame($expected->cover_image, $actual->cover_image);
        self::assertSame($expected->flags, $actual->flags);
        self::assertSame($expected->approximate_guild_count, $actual->approximate_guild_count);
        self::assertSame($expected->approximate_user_install_count, $actual->approximate_user_install_count);

        if (isset($expected->redirect_uris)) {
            self::assertSame(count($expected->redirect_uris), count($actual->redirect_uris));

            for ($i = 0; $i < count($expected->redirect_uris); ++$i) {
                self::assertSame($expected->redirect_uris[$i], $actual->redirect_uris[$i]);
            }
        } else {
            self::assertNull($actual->redirect_uris);
        }

        self::assertSame($expected->interactions_endpoint_url, $actual->interactions_endpoint_url);
        self::assertSame($expected->role_connections_verification_url, $actual->role_connections_verification_url);

        if (isset($expected->tags)) {
            self::assertSame(count($expected->tags), count($actual->tags));

            for ($i = 0; $i < count($expected->tags); ++$i) {
                self::assertSame($expected->tags[$i], $actual->tags[$i]);
            }
        } else {
            self::assertNull($actual->tags);
        }

        if (isset($expected->install_params)) {
            InstallParamsTest::assertDeepSame($expected->install_params, $actual->install_params);
        } else {
            self::assertNull($actual->install_params);
        }

        if (isset($expected->integration_types_config)) {
            self::assertSame(count($expected->integration_types_config), count($actual->integration_types_config));

            for ($i = 0; $i < count($expected->integration_types_config); ++$i) {
                ApplicationIntegrationTypeConfigurationTest::assertDeepSame($expected->integration_types_config[$i], $actual->integration_types_config[$i]);
            }
        } else {
            self::assertNull($actual->integration_types_config);
        }

        self::assertSame($expected->custom_install_url, $actual->custom_install_url);
    }

    /**
     * @return array
     */
    public static function provider_deserialization(): array
    {
        $subjectTemplate = '{"id":"test-id","name":"test-name","icon":%s,"description":"test-description","bot_public":true,"bot_require_code_grant":false,"verify_key":"test-verify-key","team":%s%s}';

        $intTypeTemplates = [];
        $intTypesExpected = [];

        foreach (ApplicationIntegrationTypeConfigurationTest::provider_deserialization() as [$intTypeTemplate, $intTypeExpected]) {
            $intTypeTemplates[] = $intTypeTemplate;
            $intTypesExpected[] = $intTypeExpected;
        }

        $data = [];

        foreach (TeamTest::provider_deserialization() as [$teamTemplate, $teamExpected]) {
            foreach (UserTest::provider_deserialization() as [$userTemplate, $userExpected]) {
                foreach (GuildTest::provider_deserialization() as [$guildTemplate, $guildExpected]) {
                    foreach (InstallParamsTest::provider_deserialization() as [$installParamsTemplate, $installParamsExpected]) {
                        $data[] = [
                            sprintf($subjectTemplate, '"test-icon"', $teamTemplate, ',"bot":' . $userTemplate . ',"owner":' . $userTemplate . ',"guild":' . $guildTemplate . ',"install_params":' . $installParamsTemplate . ',"integration_types_config":[' . implode(',', $intTypeTemplates) . ']'),
                            new Application(
                                id: 'test-id',
                                name: 'test-name',
                                icon: 'test-icon',
                                description: 'test-description',
                                bot_public: true,
                                bot_require_code_grant: false,
                                verify_key: 'test-verify-key',
                                team: $teamExpected,
                                bot: $userExpected,
                                owner: $userExpected,
                                guild: $guildExpected,
                                install_params: $installParamsExpected,
                                integration_types_config: $intTypesExpected
                            )
                        ];
                    }
                }
            }
        }

        return [
            [
                sprintf($subjectTemplate, 'null', 'null', ''),
                new Application(
                    id: 'test-id',
                    name: 'test-name',
                    icon: null,
                    description: 'test-description',
                    bot_public: true,
                    bot_require_code_grant: false,
                    verify_key: 'test-verify-key',
                    team: null
                )
            ],
            ...$data
        ];
    }

    /**
     * @param string $subject
     * @param Application $expected
     * @return void
     * @dataProvider provider_deserialization
     */
    public function test_deserialization(string $subject, Application $expected): void
    {
        self::testDeserialization($subject, $expected, Application::class);
    }
}
