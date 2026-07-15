<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\BFRPG\Entity\RulesSource;

use App\Command\BFRPG\Entity\RulesSource\ListCommand;
use App\Domain\BFRPG\Entity\RulesSource;
use Doctrine\ORM\EntityManagerInterface;
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
#[CoversClass(ListCommand::class)]
final class ListCommandTest extends KernelTestCase
{
    #[Test]
    public function it_displays_a_zero_count_when_rules_sources_not_found(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-source:list',
            ]
        );

        $appTester->assertCommandIsSuccessful();

        $this->assertStringContainsString('Found 0 rules sources.', $appTester->getDisplay());
    }

    #[Test]
    public function it_lists_multiple_rules_sources(): void
    {
        $this->bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager('bfrpg');

        $entityManager->persist((new RulesSource())->setName('Test Rules Source 1'));
        $entityManager->persist((new RulesSource())->setName('Test Rules Source 2'));
        $entityManager->flush();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-source:list',
            ]
        );

        $appTester->assertCommandIsSuccessful();
        $this->assertMatchesRegularExpression('/name\s+Test Rules Source 1/', $appTester->getDisplay());
        $this->assertMatchesRegularExpression('/name\s+Test Rules Source 2/', $appTester->getDisplay());
        $this->assertStringContainsString('Found 2 rules sources.', $appTester->getDisplay());
    }
}
