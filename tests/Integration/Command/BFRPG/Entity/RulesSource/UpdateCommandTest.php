<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\BFRPG\Entity\RulesSource;

use App\Command\BFRPG\Entity\RulesSource\UpdateCommand;
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
#[CoversClass(UpdateCommand::class)]
final class UpdateCommandTest extends KernelTestCase
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
                'command' => 'app:bfrpg:entity:rules-source:update',
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
    public function it_updates_rules_source_non_interactively(): void
    {
        $this->bootKernel();

        $entityManager = $this->getContainer()->get('doctrine')->getManager('bfrpg');
        $entityManager->persist((new RulesSource())->setName('Test Rules Source'));
        $entityManager->flush();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-source:update',
                'id' => 1,
                '--name' => 'Test Rules Source Revised',
            ],
            [
                'interactive' => false
            ]
        );

        $appTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Rules source with id 1 has been updated.', $appTester->getDisplay());

        // Verify non-persistence in the database
        $source = $entityManager->getRepository(RulesSource::class)->findOneBy(['name' => 'Test Rules Source Revised']);

        $this->assertNotNull($source, 'Rules source should be persisted in the database.');
    }

    #[Test]
    public function it_updates_rules_source_interactively(): void
    {
        $this->bootKernel();

        $entityManager = $this->getContainer()->get('doctrine')->getManager('bfrpg');
        $entityManager->persist((new RulesSource())->setName('Test Rules Source'));
        $entityManager->flush();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        // Simulate interactive prompts: name (via interact()) + confirmation (via confirm())
        $appTester->setInputs(['Test Rules Source', 'y']);
        $appTester->run(['command' => 'app:bfrpg:entity:rules-source:update', '--name' => 'Test Rules Source Revised']);

        $appTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Rules source with id 1 has been updated.', $appTester->getDisplay());

        // Verify non-persistence in the database
        $source = $entityManager->getRepository(RulesSource::class)->findOneBy(['name' => 'Test Rules Source Revised']);

        $this->assertNotNull($source, 'Rules source should be persisted in the database.');
    }

    #[Test]
    public function it_cancels_updating_rules_source_interactively(): void
    {
        $this->bootKernel();

        $entityManager = $this->getContainer()->get('doctrine')->getManager('bfrpg');
        $entityManager->persist((new RulesSource())->setName('Test Rules Source'));
        $entityManager->flush();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        // Simulate interactive prompts: name (via interact()) + confirmation (via confirm())
        $appTester->setInputs(['Test Rules Source', 'n']);
        $appTester->run(['command' => 'app:bfrpg:entity:rules-source:update', '--name' => 'Test Rules Source Revised']);

        $appTester->assertCommandIsSuccessful();

        // Verify persistence in the database
        $source = $entityManager->getRepository(RulesSource::class)->findOneBy(['name' => 'Test Rules Source']);

        $this->assertNotNull($source, 'Rules source should be persisted in the database.');
    }
}
