<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\BFRPG\Entity;

use App\Domain\BFRPG\Entity\RulesSource;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesSource::class)]
final class RulesSourceTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $source = new RulesSource();
        $this->assertNull($source->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $source = new RulesSource();
        $this->assertNull($source->getName());
        $this->assertSame($source, $source->setName('Test Source'));
        $this->assertSame('Test Source', $source->getName());
    }

    #[Test]
    public function it_has_empty_collections_initially(): void
    {
        $source = new RulesSource();
        $this->assertCount(0, $source->getItems());
        $this->assertCount(0, $source->getWeaponSizes());
        $this->assertCount(0, $source->getWeaponCategories());
        $this->assertCount(0, $source->getWeapons());
        $this->assertCount(0, $source->getRangeCategories());
        $this->assertCount(0, $source->getItemRangeCategoryDistances());
        $this->assertCount(0, $source->getWeaponRangeCategoryDistances());
        $this->assertCount(0, $source->getArmors());
    }

    #[Test]
    public function it_complies_with_collection_interface(): void
    {
        $source = new RulesSource();
        $this->assertInstanceOf(Collection::class, $source->getItems());
        $this->assertInstanceOf(Collection::class, $source->getWeaponSizes());
        $this->assertInstanceOf(Collection::class, $source->getWeaponCategories());
        $this->assertInstanceOf(Collection::class, $source->getWeapons());
        $this->assertInstanceOf(Collection::class, $source->getRangeCategories());
        $this->assertInstanceOf(Collection::class, $source->getItemRangeCategoryDistances());
        $this->assertInstanceOf(Collection::class, $source->getWeaponRangeCategoryDistances());
        $this->assertInstanceOf(Collection::class, $source->getArmors());
    }

}
