<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\Jabronibetz\Entity\Football\Competition;

use App\Command\Jabronibetz\Entity\Football\Competition\CreateCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('jabronibetz')]
#[CoversClass(CreateCommand::class)]
final class CreateCommandTest extends KernelTestCase
{
    #[Test]
    public function it_fails_creating_football_competition_if_managing_organization_does_not_exist(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:jabronibetz:football:competition:create',
                'name' => 'Test Competition',
                'short-name' => 'TC',
                'organization-id' => -1
            ]
        );

        $this->assertSame(1, $appTester->getStatusCode());
        $this->assertStringContainsString('Football organization not found', $appTester->getDisplay());
    }
}
