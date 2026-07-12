<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\BFRPG\Entity;

use App\Domain\BFRPG\Entity\RulesRangeCategory;
use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Entity\RulesWeapon;
use App\Domain\BFRPG\Entity\RulesWeaponRangeCategoryDistance;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesWeaponRangeCategoryDistance::class)]
final class RulesWeaponRangeCategoryDistanceTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $distance = new RulesWeaponRangeCategoryDistance();
        $this->assertNull($distance->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_weapon(): void
    {
        $weapon = new RulesWeapon();
        $distance = new RulesWeaponRangeCategoryDistance();
        $this->assertNull($distance->getWeapon());
        $this->assertSame($distance, $distance->setWeapon($weapon));
        $this->assertSame($weapon, $distance->getWeapon());
    }

    #[Test]
    public function it_has_getter_and_setter_for_range_category(): void
    {
        $rangeCategory = new RulesRangeCategory();
        $distance = new RulesWeaponRangeCategoryDistance();
        $this->assertNull($distance->getRangeCategory());
        $this->assertSame($distance, $distance->setRangeCategory($rangeCategory));
        $this->assertSame($rangeCategory, $distance->getRangeCategory());
    }

    #[Test]
    public function it_has_getter_and_setter_for_distance(): void
    {
        $distance = new RulesWeaponRangeCategoryDistance();
        $this->assertNull($distance->getDistance());
        $this->assertSame($distance, $distance->setDistance(30));
        $this->assertSame(30, $distance->getDistance());
    }

    #[Test]
    public function it_has_getter_and_setter_for_source(): void
    {
        $source = new RulesSource();
        $distance = new RulesWeaponRangeCategoryDistance();
        $this->assertNull($distance->getSource());
        $this->assertSame($distance, $distance->setSource($source));
        $this->assertSame($source, $distance->getSource());
    }
}
