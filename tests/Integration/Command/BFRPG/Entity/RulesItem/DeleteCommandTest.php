<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\BFRPG\Entity\RulesItem;

use App\Command\BFRPG\Entity\RulesItem\DeleteCommand;
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
#[CoversClass(DeleteCommand::class)]
final class DeleteCommandTest extends KernelTestCase
{
    #[Test]
    public function it_fails_when_rules_item_id_not_found(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-item:delete',
                'id' => -1,
            ]
        );

        $this->assertSame(1, $appTester->getStatusCode());
        $this->assertStringContainsString(
            'App\Domain\BFRPG\Entity\RulesItem not found for id -1',
            $appTester->getDisplay()
        );
    }

    #[Test]
    public function it_deletes_rules_item_non_interactively(): void
    {
        $this->bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager('bfrpg');
        $source = (new RulesSource())->setName('Test Source');
        $entityManager->persist($source);
        $entityManager->flush();
        $entityManager->persist(
            (new RulesItem())
                ->setName('Test Item')
                ->setPrice(1)
                ->setWeight(1)
                ->setSource($source)
        );
        $entityManager->flush();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-item:delete',
                'id' => 1,
            ],
            [
                'interactive' => false
            ]
        );

        $appTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Rules item with id 1 has been deleted.', $appTester->getDisplay());

        // Verify non-persistence in the database
        $item = $entityManager->getRepository(RulesItem::class)->findOneBy(['name' => 'Test Item']);
        $this->assertNull($item, 'Rules item should not be persisted in the database.');
        $source = $entityManager->getRepository(RulesSource::class)->findOneBy(['name' => 'Test Source']);
        $this->assertNotNull($source, 'Rules source should be persisted in the database.');
    }
}
