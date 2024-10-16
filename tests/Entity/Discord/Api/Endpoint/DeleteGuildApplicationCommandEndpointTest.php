<?php

declare(strict_types=1);

namespace App\Tests\Entity\Discord\Api\Endpoint;

use App\Entity\Discord\Api\Endpoint\DeleteGuildApplicationCommandEndpoint;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Entity\Discord\Api\Endpoint\DeleteGuildApplicationCommandEndpoint
 */
final class DeleteGuildApplicationCommandEndpointTest extends KernelTestCase
{
    /**
     * @return void
     */
    public function test_getRequestMethod(): void
    {
        self::assertSame('DELETE', DeleteGuildApplicationCommandEndpoint::getRequestMethod());
    }

    /**
     * @return void
     */
    public function test_getRequestBody(): void
    {
        self::assertNull(
            (new DeleteGuildApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                guildId: 'test-guild-id',
                commandId: 'test-command-id',
            ))->getRequestBody()
        );
    }

    /**
     * @return void
     */
    public function test_getRequestPath(): void
    {
        self::assertSame(
            'applications/test-application-id/guilds/test-guild-id/commands/test-command-id',
            (new DeleteGuildApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                guildId: 'test-guild-id',
                commandId: 'test-command-id',
            ))->getRequestPath()
        );
    }

    /**
     * @return void
     */
    public function test_getResponseBodyType(): void
    {
        self::assertNull(
            (new DeleteGuildApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                guildId: 'test-guild-id',
                commandId: 'test-command-id',
            ))->getResponseBodyType()
        );
    }

    /**
     * @return void
     */
    public function test_hasRequestBody(): void
    {
        self::assertFalse(
            (new DeleteGuildApplicationCommandEndpoint(
                applicationId: 'test-application-id',
                guildId: 'test-guild-id',
                commandId: 'test-command-id',
            ))->hasRequestBody()
        );
    }
}
