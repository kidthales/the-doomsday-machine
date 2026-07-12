<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\BFRPG\Entity;

use App\Domain\BFRPG\Entity\RulesSource;
use App\Domain\BFRPG\Entity\RulesWeaponCategory;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesWeaponCategory::class)]
final class RulesWeaponCategoryTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $category = new RulesWeaponCategory();
        $this->assertNull($category->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $category = new RulesWeaponCategory();
        $this->assertNull($category->getName());
        $this->assertSame($category, $category->setName('Test Category'));
        $this->assertSame('Test Category', $category->getName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_source(): void
    {
        $source = new RulesSource();
        $category = new RulesWeaponCategory();
        $this->assertNull($category->getSource());
        $this->assertSame($category, $category->setSource($source));
        $this->assertSame($source, $category->getSource());
    }

    #[Test]
    public function it_initializes_weapons_as_empty_collection(): void
    {
        $category = new RulesWeaponCategory();
        $this->assertCount(0, $category->getWeapons());
    }

    #[Test]
    public function it_returns_collection_interface_for_weapons(): void
    {
        $category = new RulesWeaponCategory();
        $this->assertInstanceOf(Collection::class, $category->getWeapons());
    }
}
