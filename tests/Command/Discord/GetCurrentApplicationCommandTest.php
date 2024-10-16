<?php

declare(strict_types=1);

namespace App\Tests\Command\Discord;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \App\Command\Discord\GetCurrentApplicationCommand
 */
final class GetCurrentApplicationCommandTest extends KernelTestCase
{
    /**
     * @return void
     */
    public function test_execute(): void
    {
        self::bootKernel();

        $application = new Application(self::$kernel);

        $command = $application->find('app:discord:get-current-application');

        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        $actual = $commandTester->getDisplay();

        self::assertStringContainsString(getenv('DISCORD_APP_NAME'), $actual);
    }
}
