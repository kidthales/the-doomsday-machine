<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\Jabronibetz\Entity\FootballTeam;

use App\Command\Jabronibetz\Entity\FootballTeam\CreateCommand;
use App\Domain\Jabronibetz\Enum\FootballGender;
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
    public function it_fails_creating_football_team_if_managing_organization_does_not_exist(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:jabronibetz:entity:football-team:create',
                'name' => 'Test Team',
                'short-name' => 'TT',
                'organization-id' => -1,
                'gender' => FootballGender::Male->value
            ]
        );

        $this->assertSame(1, $appTester->getStatusCode());
        $this->assertStringContainsString('Football organization not found', $appTester->getDisplay());
    }
}
