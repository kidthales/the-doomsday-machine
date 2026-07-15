<?php

declare(strict_types=1);

namespace App\Tests\Integration\Command\BFRPG\Entity\RulesSource;

use App\Command\BFRPG\Entity\RulesSource\DeleteCommand;
use App\Domain\BFRPG\Entity\RulesArmor;
use App\Domain\BFRPG\Entity\RulesItem;
use App\Domain\BFRPG\Entity\RulesItemRangeCategoryDistance;
use App\Domain\BFRPG\Entity\RulesRangeCategory;
use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Entity\RulesWeapon;
use App\Domain\BFRPG\Entity\RulesWeaponCategory;
use App\Domain\BFRPG\Entity\RulesWeaponRangeCategoryDistance;
use App\Domain\BFRPG\Entity\RulesWeaponSize;
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
    public function it_fails_when_rules_source_id_not_found(): void
    {
        $this->bootKernel();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(
            [
                'command' => 'app:bfrpg:entity:rules-source:delete',
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
    public function it_deletes_rules_source_non_interactively(): void
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
                'command' => 'app:bfrpg:entity:rules-source:delete',
                'id' => 1,
            ],
            [
                'interactive' => false
            ]
        );

        $appTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Rules source with id 1 has been deleted.', $appTester->getDisplay());

        // Verify non-persistence in the database
        $source = $entityManager->getRepository(RulesSource::class)->findOneBy(['name' => 'Test Rules Source']);

        $this->assertNull($source, 'Rules source should not be persisted in the database.');
    }

    #[Test]
    public function it_deletes_rules_source_interactively(): void
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
        $appTester->run(['command' => 'app:bfrpg:entity:rules-source:delete']);

        $appTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Rules source with id 1 has been deleted.', $appTester->getDisplay());

        // Verify non-persistence in the database
        $source = $entityManager->getRepository(RulesSource::class)->findOneBy(['name' => 'Test Rules Source']);

        $this->assertNull($source, 'Rules source should not be persisted in the database.');
    }

    #[Test]
    public function it_cancels_deleting_rules_source_interactively(): void
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
        $appTester->run(['command' => 'app:bfrpg:entity:rules-source:delete']);

        $appTester->assertCommandIsSuccessful();

        // Verify persistence in the database
        $source = $entityManager->getRepository(RulesSource::class)->findOneBy(['name' => 'Test Rules Source']);

        $this->assertNotNull($source, 'Rules source should be persisted in the database.');
        $this->assertSame('Test Rules Source', $source->getName());
    }

    #[Test]
    public function it_deletes_rules_source_and_cascades(): void
    {
        $this->bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getContainer()->get('doctrine')->getManager('bfrpg');

        $source = (new RulesSource())->setName('Test Rules Source');
        $entityManager->persist($source);
        $entityManager->flush();

        $item = (new RulesItem())
            ->setName('Test Rules Item')
            ->setPrice(1)
            ->setWeight(1)
            ->setSource($source);
        $entityManager->persist($item);
        $weaponSize = (new RulesWeaponSize())
            ->setName('Test')
            ->setShortName('7XL')
            ->setSource($source);
        $entityManager->persist($weaponSize);
        $weaponCategory = (new RulesWeaponCategory())
            ->setName('Test')
            ->setSource($source);
        $entityManager->persist($weaponCategory);
        $weapon = (new RulesWeapon())
            ->setName('Test Weapon')
            ->setPrice(1)
            ->setWeight(1)
            ->setSource($source);
        $entityManager->persist($weapon);
        $rangeCategory = (new RulesRangeCategory())
            ->setName('Test')
            ->setModifier(0)
            ->setSource($source);
        $entityManager->persist($rangeCategory);
        $itemRangeCategoryDistance = (new RulesItemRangeCategoryDistance())
            ->setItem($item)
            ->setRangeCategory($rangeCategory)
            ->setDistance(1)
            ->setSource($source);
        $entityManager->persist($itemRangeCategoryDistance);
        $weaponRangeCategoryDistance = (new RulesWeaponRangeCategoryDistance())
            ->setWeapon($weapon)
            ->setRangeCategory($rangeCategory)
            ->setDistance(1)
            ->setSource($source);
        $entityManager->persist($weaponRangeCategoryDistance);
        $armor = (new RulesArmor())
            ->setName('Test Armor')
            ->setPrice(1)
            ->setWeight(1)
            ->setSource($source);
        $entityManager->persist($armor);

        $entityManager->flush();
        $entityManager->clear();

        $app = new Application(self::$kernel);
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->setInputs(['Test Rules Source', 'y']);
        $appTester->run(['command' => 'app:bfrpg:entity:rules-source:delete']);

        $appTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('1 rules items will also be deleted!', $appTester->getDisplay());
        $this->assertStringContainsString('1 rules weapon sizes will also be deleted!', $appTester->getDisplay());
        $this->assertStringContainsString('1 rules weapon categories will also be deleted!', $appTester->getDisplay());
        $this->assertStringContainsString('1 rules weapons will also be deleted!', $appTester->getDisplay());
        $this->assertStringContainsString('1 rules range categories will also be deleted!', $appTester->getDisplay());
        $this->assertStringContainsString(
            '1 rules item range category distances will also be deleted!',
            $appTester->getDisplay()
        );
        $this->assertStringContainsString(
            '1 rules weapon range category distances will also be deleted!',
            $appTester->getDisplay()
        );
        $this->assertStringContainsString('1 rules armors will also be deleted!', $appTester->getDisplay());
        $this->assertStringContainsString('Rules source with id 1 has been deleted.', $appTester->getDisplay());

        // Verify non-persistence in the database
        $this->assertSame(0, $entityManager->getRepository(RulesSource::class)->count());
        $this->assertSame(0, $entityManager->getRepository(RulesItem::class)->count());
        $this->assertSame(0, $entityManager->getRepository(RulesWeaponSize::class)->count());
        $this->assertSame(0, $entityManager->getRepository(RulesWeaponCategory::class)->count());
        $this->assertSame(0, $entityManager->getRepository(RulesWeapon::class)->count());
        $this->assertSame(0, $entityManager->getRepository(RulesRangeCategory::class)->count());
        $this->assertSame(0, $entityManager->getRepository(RulesItemRangeCategoryDistance::class)->count());
        $this->assertSame(0, $entityManager->getRepository(RulesWeaponRangeCategoryDistance::class)->count());
        $this->assertSame(0, $entityManager->getRepository(RulesArmor::class)->count());
    }
}
