<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\Jabronibetz\Football\Organization;

use App\Command\Jabronibetz\Football\Organization\CreateCommand;
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
    public function it_creates_football_organization_non_interactively(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:jabronibetz:football:organization:create',
                'name' => 'Test Association',
                'short-name' => 'TA',
            ],
            [
                'interactive' => false
            ]
        );

        $appTester->assertCommandIsSuccessful('Football organization Test Association (TA) has been created with id 1.');
    }
}
