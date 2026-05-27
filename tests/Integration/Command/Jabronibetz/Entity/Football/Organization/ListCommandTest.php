<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\Jabronibetz\Entity\Football\Organization;

use App\Command\Jabronibetz\Entity\Football\Organization\ListCommand;
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
#[CoversClass(ListCommand::class)]
final class ListCommandTest extends KernelTestCase
{
    #[Test]
    public function it_displays_a_count_of_football_organizations_found(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:jabronibetz:football:organization:list',
            ]
        );

        $appTester->assertCommandIsSuccessful();

        $this->assertStringContainsString('Found 0 football organizations.', $appTester->getDisplay());
    }
}
