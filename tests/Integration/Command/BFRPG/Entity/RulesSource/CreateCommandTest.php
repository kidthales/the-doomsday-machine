<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\BFRPG\Entity\RulesSource;

use App\Command\BFRPG\Entity\RulesSource\CreateCommand;
use App\Domain\BFRPG\Entity\RulesSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(CreateCommand::class)]
final class CreateCommandTest extends KernelTestCase
{
    #[Test]
    public function it_creates_rules_source_non_interactively(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-source:create',
                'name' => 'Test Source'
            ],
            [
                'interactive' => false
            ]
        );

        $appTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Rules source created with id 1.', $appTester->getDisplay());
    }

    #[Test]
    public function it_creates_rules_source_interactively(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        // Simulate interactive prompts: name (via interact()) + confirmation (via confirm())
        $appTester->setInputs(['Test Rules Source', 'y']);
        $appTester->run(['command' => 'app:bfrpg:entity:rules-source:create']);

        $appTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Rules source created with id 1.', $appTester->getDisplay());

        // Verify persistence in the database
        $entityManager = self::$kernel->getContainer()->get('doctrine')->getManager('bfrpg');
        $source = $entityManager->getRepository(RulesSource::class)->findOneBy(['name' => 'Test Rules Source']);

        $this->assertNotNull($source, 'Rules source should be persisted in the database.');
        $this->assertSame('Test Rules Source', $source->getName());
    }

    #[Test]
    public function it_cancels_creating_rules_source_interactively(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        // Simulate interactive prompts: name (via interact()) + confirmation (via confirm())
        $appTester->setInputs(['Test Rules Source', 'n']);
        $appTester->run(['command' => 'app:bfrpg:entity:rules-source:create']);

        $appTester->assertCommandIsSuccessful();

        // Verify non-persistence in the database
        $entityManager = $this->getContainer()->get('doctrine')->getManager('bfrpg');
        $source = $entityManager->getRepository(RulesSource::class)->findOneBy(['name' => 'Test Rules Source']);

        $this->assertNull($source, 'Rules source should not be persisted in the database.');
    }

    #[Test]
    public function it_returns_error_when_name_is_empty_string(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-source:create',
                'name' => ''
            ]
        );

        $this->assertSame(1, $appTester->getStatusCode());
        $this->assertStringContainsString('This value should not be blank.', $appTester->getDisplay());
        $this->assertStringContainsString(
            'This value is too short. It should have 1 character or more.',
            $appTester->getDisplay()
        );
    }

    #[Test]
    public function it_returns_error_when_name_length_is_greater_than_limit(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-source:create',
                'name' => str_repeat('Test Source', 100)
            ]
        );

        $this->assertSame(1, $appTester->getStatusCode());
        $this->assertStringContainsString(
            'This value is too long. It should have 255 characters or less.',
            $appTester->getDisplay()
        );
    }
}
