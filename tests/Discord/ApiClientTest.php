<?php

declare(strict_types=1);

namespace App\Tests\Discord;

use App\Discord\ApiClient;
use App\Entity\Discord\Api\Dto\Application;
use App\Entity\Discord\Api\Dto\ApplicationCommand;
use App\Entity\Discord\Api\Dto\CreateGlobalApplicationCommandParams;
use App\Entity\Discord\Api\Dto\CreateGuildApplicationCommandParams;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;
use ValueError;

/**
 * @covers \App\Discord\ApiClient
 */
final class ApiClientTest extends KernelTestCase
{
    /**
     * @return void
     */
    public function test_defaultApiVersion(): void
    {
        self::bootKernel();

        $subject = new ApiClient(
            self::getContainer()->get(HttpClientInterface::class),
            self::getContainer()->get(SerializerInterface::class),
        );

        self::assertSame(ApiClient::V10, $subject->getApiVersion());
    }

    /**
     * @return array
     */
    public static function provider_apiVersion(): array
    {
        return [
            ...array_map(fn (string $v) => [$v, $v], ApiClient::ALLOWED_VERSIONS),
            ['v1', new ValueError('Invalid api version: v1; allowed versions: ' . implode(', ', ApiClient::ALLOWED_VERSIONS))],
        ];
    }

    /**
     * @param string $apiVersion
     * @param string|ValueError $expected
     * @return void
     * @dataProvider provider_apiVersion
     */
    public function test_apiVersion(string $apiVersion, string|ValueError $expected): void
    {
        self::bootKernel();

        $subject = new ApiClient(
            self::getContainer()->get(HttpClientInterface::class),
            self::getContainer()->get(SerializerInterface::class),
        );

        try {
            $subject->setApiVersion($apiVersion);

            if ($expected instanceof ValueError) {
                self::fail('Expected exception not thrown.');
            }

            $actual = $subject->getApiVersion();

            self::assertSame($expected, $actual);
        } catch (Throwable $e) {
            if (!($expected instanceof ValueError)) {
                self::fail('Unexpected exception: ' . $e->getMessage());
            }

            self::assertInstanceOf(ValueError::class, $e);
            self::assertSame($expected->getMessage(), $e->getMessage());
        }
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function test_createAndDeleteGlobalApplicationCommand(): void
    {
        self::bootKernel();

        /** @var ApiClient $subject */
        $subject = self::getContainer()->get(ApiClient::class);

        $isOverwrite = null;

        $actual = $subject->createGlobalApplicationCommand(
            getenv('DISCORD_APP_ID'),
            new CreateGlobalApplicationCommandParams(
                name: 'test-global-app',
                description: 'Test global application command'
            ),
           $isOverwrite
        );

        self::assertIsBool($isOverwrite);
        self::assertInstanceOf(ApplicationCommand::class, $actual);

        sleep(1);

        $actual = $subject->deleteGlobalApplicationCommand(getenv('DISCORD_APP_ID'), $actual->id);

        self::assertNull($actual);
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function test_createAndDeleteGuildApplicationCommand(): void
    {
        self::bootKernel();

        /** @var ApiClient $subject */
        $subject = self::getContainer()->get(ApiClient::class);

        $isOverwrite = null;

        $actual = $subject->createGuildApplicationCommand(
            getenv('DISCORD_APP_ID'),
            getenv('DISCORD_DEV_GUILD_ID'),
            new CreateGuildApplicationCommandParams(
                name: 'test-global-app',
                description: 'Test global application command'
            ),
            $isOverwrite
        );

        self::assertIsBool($isOverwrite);
        self::assertInstanceOf(ApplicationCommand::class, $actual);

        sleep(1);

        $actual = $subject->deleteGuildApplicationCommand(
            getenv('DISCORD_APP_ID'),
            getenv('DISCORD_DEV_GUILD_ID'),
            $actual->id
        );

        self::assertNull($actual);
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function test_getCurrentApplication(): void
    {
        self::bootKernel();

        /** @var ApiClient $subject */
        $subject = self::getContainer()->get(ApiClient::class);

        $actual = $subject->getCurrentApplication();

        self::assertInstanceOf(Application::class, $actual);
        self::assertNotEmpty($actual->id);
    }
}
