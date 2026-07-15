<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\BFRPG\Entity\RulesSource;

use App\Command\BFRPG\Entity\RulesSource\ReadCommand;
use App\Domain\BFRPG\Entity\RulesSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('bfrpg')]
#[CoversClass(ReadCommand::class)]
final class ReadCommandTest extends KernelTestCase
{
    #[Test]
    public function it_fails_when_rules_source_id_not_found(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-source:read',
                'id' => -1,
            ]
        );

        $this->assertSame(1, $appTester->getStatusCode());
        $this->assertStringContainsString(
            'App\Domain\BFRPG\Entity\RulesSource not found for id -1',
            $appTester->getDisplay()
        );
    }

    #[Test]
    public function it_reads_rules_source(): void
    {
        $this->bootKernel();

        $entityManager = self::$kernel->getContainer()->get('doctrine')->getManager('bfrpg');
        $entityManager->persist((new RulesSource())->setName('Test Rules Source'));
        $entityManager->flush();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-source:read',
                'id' => 1,
            ]
        );

        $appTester->assertCommandIsSuccessful();
        $this->assertMatchesRegularExpression('/id\s+1/', $appTester->getDisplay());
        $this->assertMatchesRegularExpression('/name\s+Test Rules Source/', $appTester->getDisplay());
    }
}
