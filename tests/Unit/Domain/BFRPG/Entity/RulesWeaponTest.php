<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\BFRPG\Entity;

use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Entity\RulesWeapon;
use App\Domain\BFRPG\Entity\RulesWeaponCategory;
use App\Domain\BFRPG\Entity\RulesWeaponSize;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesWeapon::class)]
final class RulesWeaponTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getName());
        $this->assertSame($weapon, $weapon->setName('Test Weapon'));
        $this->assertSame('Test Weapon', $weapon->getName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_price(): void
    {
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getPrice());
        $this->assertSame($weapon, $weapon->setPrice(17.17));
        $this->assertSame(17.17, $weapon->getPrice());
    }

    #[Test]
    public function it_has_getter_and_setter_for_weight(): void
    {
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getWeight());
        $this->assertSame($weapon, $weapon->setWeight(17.17));
        $this->assertSame(17.17, $weapon->getWeight());
    }

    #[Test]
    public function it_has_getter_and_setter_for_description(): void
    {
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getDescription());
        $this->assertSame($weapon, $weapon->setDescription('Test description.'));
        $this->assertSame('Test description.', $weapon->getDescription());
    }

    #[Test]
    public function it_has_getter_and_setter_for_weapon_size(): void
    {
        $size = new RulesWeaponSize();
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getWeaponSize());
        $this->assertSame($weapon, $weapon->setWeaponSize($size));
        $this->assertSame($size, $weapon->getWeaponSize());
    }

    #[Test]
    public function it_has_getter_and_setter_for_damage_roll(): void
    {
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getDamageRoll());
        $this->assertSame($weapon, $weapon->setDamageRoll('1d6'));
        $this->assertSame('1d6', $weapon->getDamageRoll());
    }

    #[Test]
    public function it_has_getter_and_setter_for_missile_damage_roll(): void
    {
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getMissileDamageRoll());
        $this->assertSame($weapon, $weapon->setMissileDamageRoll('1d8'));
        $this->assertSame('1d8', $weapon->getMissileDamageRoll());
    }

    #[Test]
    public function it_has_getter_and_setter_for_one_handed_damage_roll(): void
    {
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getOneHandedDamageRoll());
        $this->assertSame($weapon, $weapon->setOneHandedDamageRoll('1d4'));
        $this->assertSame('1d4', $weapon->getOneHandedDamageRoll());
    }

    #[Test]
    public function it_has_getter_and_setter_for_two_handed_damage_roll(): void
    {
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getTwoHandedDamageRoll());
        $this->assertSame($weapon, $weapon->setTwoHandedDamageRoll('2d6'));
        $this->assertSame('2d6', $weapon->getTwoHandedDamageRoll());
    }

    #[Test]
    public function it_has_getter_and_setter_for_weapon_category(): void
    {
        $category = new RulesWeaponCategory();
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getWeaponCategory());
        $this->assertSame($weapon, $weapon->setWeaponCategory($category));
        $this->assertSame($category, $weapon->getWeaponCategory());
    }

    #[Test]
    public function it_has_getter_and_setter_for_source(): void
    {
        $source = new RulesSource();
        $weapon = new RulesWeapon();
        $this->assertNull($weapon->getSource());
        $this->assertSame($weapon, $weapon->setSource($source));
        $this->assertSame($source, $weapon->getSource());
    }

    #[Test]
    public function it_initializes_weapon_range_category_distances_as_empty_collection(): void
    {
        $weapon = new RulesWeapon();
        $this->assertCount(0, $weapon->getWeaponRangeCategoryDistances());
    }

    #[Test]
    public function it_returns_collection_interface_for_weapon_range_category_distances(): void
    {
        $weapon = new RulesWeapon();
        $this->assertInstanceOf(Collection::class, $weapon->getWeaponRangeCategoryDistances());
    }
}
