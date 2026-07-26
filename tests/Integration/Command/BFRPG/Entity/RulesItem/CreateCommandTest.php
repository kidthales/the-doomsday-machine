<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\BFRPG\Entity\RulesItem;

use App\Command\BFRPG\Entity\RulesItem\CreateCommand;
use App\Domain\BFRPG\Entity\RulesItem;
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
#[CoversClass(CreateCommand::class)]
final class CreateCommandTest extends KernelTestCase
{
    #[Test]
    public function it_fails_creating_rules_item_if_source_does_not_exist(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-item:create',
                'name' => 'Test Item',
                'price' => 1.0,
                'weight' => 1.0,
                'source-id' => -1
            ]
        );

        $this->assertSame(1, $appTester->getStatusCode());
        $this->assertStringContainsString(
            'App\Domain\BFRPG\Entity\RulesSource not found for id -1',
            $appTester->getDisplay()
        );
    }

    #[Test]
    public function it_creates_rules_source_non_interactively(): void
    {
        $this->bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager('bfrpg');
        $entityManager->persist((new RulesSource())->setName('Test Source'));
        $entityManager->flush();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-item:create',
                'name' => 'Test Item',
                'price' => 1.0,
                'weight' => 1.0,
                'source-id' => 1
            ],
            [
                'interactive' => false
            ]
        );

        $appTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Rules item has been created with id 1.', $appTester->getDisplay());

        // Verify persistence in the database
        $item = $entityManager->getRepository(RulesItem::class)->findOneBy(['name' => 'Test Item']);
        $this->assertNotNull($item, 'Rules item should be persisted in the database.');
    }

    #[Test]
    public function it_creates_rules_item_interactively(): void
    {
        $this->bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager('bfrpg');
        $entityManager->persist((new RulesSource())->setName('Test Source'));
        $entityManager->flush();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        // Simulate interactive prompts: name (via interact()) + confirmation (via confirm())
        $appTester->setInputs(['y']);
        $appTester->run([
            'command' => 'app:bfrpg:entity:rules-item:create',
            'name' => 'Test Item',
            'price' => 1.0,
            'weight' => 1.0,
            'source-id' => 1
        ]);

        $appTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Rules item has been created with id 1.', $appTester->getDisplay());

        // Verify persistence in the database
        $item = $entityManager->getRepository(RulesItem::class)->findOneBy(['name' => 'Test Item']);
        $this->assertNotNull($item, 'Rules item should be persisted in the database.');
    }

    #[Test]
    public function it_cancels_creating_rules_item_interactively(): void
    {
        $this->bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager('bfrpg');
        $entityManager->persist((new RulesSource())->setName('Test Source'));
        $entityManager->flush();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        // Simulate interactive prompts: name (via interact()) + confirmation (via confirm())
        $appTester->setInputs(['n']);
        $appTester->run([
            'command' => 'app:bfrpg:entity:rules-item:create',
            'name' => 'Test Item',
            'price' => 1.0,
            'weight' => 1.0,
            'source-id' => 1
        ]);

        $appTester->assertCommandIsSuccessful();

        // Verify non-persistence in the database
        $item = $entityManager->getRepository(RulesItem::class)->findOneBy(['name' => 'Test Item']);
        $this->assertNull($item, 'Rules item should not be persisted in the database.');
    }
}
