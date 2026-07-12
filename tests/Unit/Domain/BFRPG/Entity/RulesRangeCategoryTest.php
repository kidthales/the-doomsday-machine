<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\BFRPG\Entity;

use App\Domain\BFRPG\Entity\RulesRangeCategory;
use App\Domain\BFRPG\Entity\RulesSource;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesRangeCategory::class)]
final class RulesRangeCategoryTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $category = new RulesRangeCategory();
        $this->assertNull($category->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $category = new RulesRangeCategory();
        $this->assertNull($category->getName());
        $this->assertSame($category, $category->setName('Test Range'));
        $this->assertSame('Test Range', $category->getName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_modifier(): void
    {
        $category = new RulesRangeCategory();
        $this->assertNull($category->getModifier());
        $this->assertSame($category, $category->setModifier(5));
        $this->assertSame(5, $category->getModifier());
    }

    #[Test]
    public function it_has_getter_and_setter_for_source(): void
    {
        $source = new RulesSource();
        $category = new RulesRangeCategory();
        $this->assertNull($category->getSource());
        $this->assertSame($category, $category->setSource($source));
        $this->assertSame($source, $category->getSource());
    }

    #[Test]
    public function it_initializes_item_range_category_distances_as_empty_collection(): void
    {
        $category = new RulesRangeCategory();
        $this->assertCount(0, $category->getItemRangeCategoryDistances());
    }

    #[Test]
    public function it_returns_collection_interface_for_item_range_category_distances(): void
    {
        $category = new RulesRangeCategory();
        $this->assertInstanceOf(Collection::class, $category->getItemRangeCategoryDistances());
    }

    #[Test]
    public function it_initializes_weapon_range_category_distances_as_empty_collection(): void
    {
        $category = new RulesRangeCategory();
        $this->assertCount(0, $category->getWeaponRangeCategoryDistances());
    }

    #[Test]
    public function it_returns_collection_interface_for_weapon_range_category_distances(): void
    {
        $category = new RulesRangeCategory();
        $this->assertInstanceOf(Collection::class, $category->getWeaponRangeCategoryDistances());
    }
}
