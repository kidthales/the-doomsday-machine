<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\BFRPG\Entity;

use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Entity\RulesWeaponSize;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesWeaponSize::class)]
final class RulesWeaponSizeTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $size = new RulesWeaponSize();
        $this->assertNull($size->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $size = new RulesWeaponSize();
        $this->assertNull($size->getName());
        $this->assertSame($size, $size->setName('Test Size'));
        $this->assertSame('Test Size', $size->getName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_short_name(): void
    {
        $size = new RulesWeaponSize();
        $this->assertNull($size->getShortName());
        $this->assertSame($size, $size->setShortName('S'));
        $this->assertSame('S', $size->getShortName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_source(): void
    {
        $source = new RulesSource();
        $size = new RulesWeaponSize();
        $this->assertNull($size->getSource());
        $this->assertSame($size, $size->setSource($source));
        $this->assertSame($source, $size->getSource());
    }

    #[Test]
    public function it_initializes_weapons_collection_in_constructor(): void
    {
        $size = new RulesWeaponSize();
        $this->assertCount(0, $size->getWeapons());
    }
}
