<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\BFRPG\Entity;

use App\Domain\BFRPG\Entity\RulesArmor;
use App\Domain\BFRPG\Entity\RulesSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesArmor::class)]
final class RulesArmorTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $armor = new RulesArmor();
        $this->assertNull($armor->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $armor = new RulesArmor();
        $this->assertNull($armor->getName());
        $this->assertSame($armor, $armor->setName('Test Armor'));
        $this->assertSame('Test Armor', $armor->getName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_price(): void
    {
        $armor = new RulesArmor();
        $this->assertNull($armor->getPrice());
        $this->assertSame($armor, $armor->setPrice(100.50));
        $this->assertSame(100.50, $armor->getPrice());
    }

    #[Test]
    public function it_has_getter_and_setter_for_weight(): void
    {
        $armor = new RulesArmor();
        $this->assertNull($armor->getWeight());
        $this->assertSame($armor, $armor->setWeight(25.0));
        $this->assertSame(25.0, $armor->getWeight());
    }

    #[Test]
    public function it_has_getter_and_setter_for_description(): void
    {
        $armor = new RulesArmor();
        $this->assertNull($armor->getDescription());
        $this->assertSame($armor, $armor->setDescription('A sturdy plate'));
        $this->assertSame('A sturdy plate', $armor->getDescription());
    }

    #[Test]
    public function it_has_getter_and_setter_for_ac(): void
    {
        $armor = new RulesArmor();
        $this->assertNull($armor->getAC());
        $this->assertSame($armor, $armor->setAC(18));
        $this->assertSame(18, $armor->getAC());
    }

    #[Test]
    public function it_has_getter_and_setter_for_ac_bonus(): void
    {
        $armor = new RulesArmor();
        $this->assertNull($armor->getACBonus());
        $this->assertSame($armor, $armor->setACBonus(2));
        $this->assertSame(2, $armor->getACBonus());
    }

    #[Test]
    public function it_has_getter_and_setter_for_source(): void
    {
        $source = new RulesSource();
        $armor = new RulesArmor();
        $this->assertNull($armor->getSource());
        $this->assertSame($armor, $armor->setSource($source));
        $this->assertSame($source, $armor->getSource());
    }
}
