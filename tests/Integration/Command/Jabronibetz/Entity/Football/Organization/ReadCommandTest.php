<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\Jabronibetz\Entity\Football\Organization;

use App\Command\Jabronibetz\Entity\Football\Organization\ReadCommand;
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
#[CoversClass(ReadCommand::class)]
final class ReadCommandTest extends KernelTestCase
{
    #[Test]
    public function it_fails_when_football_organization_id_not_found(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:jabronibetz:football:organization:read',
                'id' => -1,
            ]
        );

        $this->assertSame(1, $appTester->getStatusCode());
        $this->assertStringContainsString('Football organization not found', $appTester->getDisplay());
    }
}
