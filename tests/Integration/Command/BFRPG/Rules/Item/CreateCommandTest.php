<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\BFRPG\Rules\Item;

use App\Command\BFRPG\Rules\Item\CreateCommand;
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
                'command' => 'app:bfrpg:rules:item:create',
                'name' => 'Test Item',
                'price' => 1.0,
                'weight' => 1.0,
                'source-id' => -1
            ]
        );

        $this->assertSame(1, $appTester->getStatusCode());
        $this->assertStringContainsString('Rules source not found', $appTester->getDisplay());
    }
}
