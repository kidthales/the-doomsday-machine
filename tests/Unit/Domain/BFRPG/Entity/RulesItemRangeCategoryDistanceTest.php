<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\BFRPG\Entity;

use App\Domain\BFRPG\Entity\RulesItem;
use App\Domain\BFRPG\Entity\RulesItemRangeCategoryDistance;
use App\Domain\BFRPG\Entity\RulesRangeCategory;
use App\Domain\BFRPG\Entity\RulesSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesItemRangeCategoryDistance::class)]
final class RulesItemRangeCategoryDistanceTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $distance = new RulesItemRangeCategoryDistance();
        $this->assertNull($distance->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_item(): void
    {
        $item = new RulesItem();
        $distance = new RulesItemRangeCategoryDistance();
        $this->assertNull($distance->getItem());
        $this->assertSame($distance, $distance->setItem($item));
        $this->assertSame($item, $distance->getItem());
    }

    #[Test]
    public function it_has_getter_and_setter_for_range_category(): void
    {
        $rangeCategory = new RulesRangeCategory();
        $distance = new RulesItemRangeCategoryDistance();
        $this->assertNull($distance->getRangeCategory());
        $this->assertSame($distance, $distance->setRangeCategory($rangeCategory));
        $this->assertSame($rangeCategory, $distance->getRangeCategory());
    }

    #[Test]
    public function it_has_getter_and_setter_for_distance(): void
    {
        $distance = new RulesItemRangeCategoryDistance();
        $this->assertNull($distance->getDistance());
        $this->assertSame($distance, $distance->setDistance(30));
        $this->assertSame(30, $distance->getDistance());
    }

    #[Test]
    public function it_has_getter_and_setter_for_source(): void
    {
        $source = new RulesSource();
        $distance = new RulesItemRangeCategoryDistance();
        $this->assertNull($distance->getSource());
        $this->assertSame($distance, $distance->setSource($source));
        $this->assertSame($source, $distance->getSource());
    }

    #[Test]
    public function it_returns_null_for_item_name_when_item_is_not_set(): void
    {
        $distance = new RulesItemRangeCategoryDistance();
        $this->assertNull($distance->getItemName());
    }

    #[Test]
    public function it_returns_item_name_when_item_is_set(): void
    {
        $item = $this->createStub(RulesItem::class);
        $item->method('getName')->willReturn('Longsword');

        $distance = new RulesItemRangeCategoryDistance();
        $distance->setItem($item);

        $this->assertSame('Longsword', $distance->getItemName());
    }

    #[Test]
    public function it_returns_null_for_range_category_name_when_category_is_not_set(): void
    {
        $distance = new RulesItemRangeCategoryDistance();
        $this->assertNull($distance->getRangeCategoryName());
    }

    #[Test]
    public function it_returns_range_category_name_when_category_is_set(): void
    {
        $category = $this->createStub(RulesRangeCategory::class);
        $category->method('getName')->willReturn('Melee');

        $distance = new RulesItemRangeCategoryDistance();
        $distance->setRangeCategory($category);

        $this->assertSame('Melee', $distance->getRangeCategoryName());
    }
}
