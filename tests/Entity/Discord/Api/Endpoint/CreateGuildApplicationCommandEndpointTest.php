<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Endpoint;

use App\Entity\Discord\Api\Dto\ApplicationCommand;
use App\Entity\Discord\Api\Dto\CreateGuildApplicationCommandParams;
use App\Entity\Discord\Api\Endpoint\CreateGuildApplicationCommandEndpoint;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Entity\Discord\Api\Endpoint\CreateGuildApplicationCommandEndpoint
 */
final class CreateGuildApplicationCommandEndpointTest extends KernelTestCase
{
    /**
     * @return array[]
     */
    public static function provider_params(): array
    {
        return [[new CreateGuildApplicationCommandParams(name: 'test-name')]];
    }

    /**
     * @return void
     */
    public function test_getRequestMethod(): void
    {
        self::assertSame('POST', CreateGuildApplicationCommandEndpoint::getRequestMethod());
    }

    /**
     * @param CreateGuildApplicationCommandParams $params
     * @return void
     * @dataProvider provider_params
     */
    public function test_getRequestBody(CreateGuildApplicationCommandParams $params): void
    {
        self::assertSame(
            $params,
            (new CreateGuildApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                guildId: 'test-guild-id',
                params: $params
            ))->getRequestBody()
        );
    }

    /**
     * @param CreateGuildApplicationCommandParams $params
     * @return void
     * @dataProvider provider_params
     */
    public function test_getRequestPath(CreateGuildApplicationCommandParams $params): void
    {
        self::assertSame(
            'applications/test-application-id/guilds/test-guild-id/commands',
            (new CreateGuildApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                guildId: 'test-guild-id',
                params: $params
            ))->getRequestPath()
        );
    }

    /**
     * @param CreateGuildApplicationCommandParams $params
     * @return void
     * @dataProvider provider_params
     */
    public function test_getResponseBodyType(CreateGuildApplicationCommandParams $params): void
    {
        self::assertSame(
            ApplicationCommand::class,
            (new CreateGuildApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                guildId: 'test-guild-id',
                params: $params
            ))->getResponseBodyType()
        );
    }

    /**
     * @param CreateGuildApplicationCommandParams $params
     * @return void
     * @dataProvider provider_params
     */
    public function test_hasRequestBody(CreateGuildApplicationCommandParams $params): void
    {
        self::assertTrue(
            (new CreateGuildApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                guildId: 'test-guild-id',
                params: $params
            ))->hasRequestBody()
        );
    }
}
