<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\BFRPG\Entity;

use App\Domain\BFRPG\Entity\RulesItem;
use App\Domain\BFRPG\Entity\RulesSource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('bfrpg')]
#[CoversClass(RulesItem::class)]
final class RulesItemTest extends TestCase
{
    #[Test]
    public function it_has_getter_for_id(): void
    {
        $item = new RulesItem();
        $this->assertNull($item->getId());
    }

    #[Test]
    public function it_has_getter_and_setter_for_name(): void
    {
        $item = new RulesItem();
        $this->assertNull($item->getName());
        $this->assertSame($item, $item->setName('Test Item'));
        $this->assertSame('Test Item', $item->getName());
    }

    #[Test]
    public function it_has_getter_and_setter_for_price(): void
    {
        $item = new RulesItem();
        $this->assertNull($item->getPrice());
        $this->assertSame($item, $item->setPrice(17.17));
        $this->assertSame(17.17, $item->getPrice());
    }

    #[Test]
    public function it_has_getter_and_setter_for_weight(): void
    {
        $item = new RulesItem();
        $this->assertNull($item->getWeight());
        $this->assertSame($item, $item->setWeight(17.17));
        $this->assertSame(17.17, $item->getWeight());
    }

    #[Test]
    public function it_has_getter_and_setter_for_description(): void
    {
        $item = new RulesItem();
        $this->assertNull($item->getDescription());
        $this->assertSame($item, $item->setDescription('Test description.'));
        $this->assertSame('Test description.', $item->getDescription());
    }

    #[Test]
    public function it_has_getter_and_setter_for_source(): void
    {
        $source = new RulesSource();
        $item = new RulesItem();
        $this->assertNull($item->getSource());
        $this->assertSame($item, $item->setSource($source));
        $this->assertSame($source, $item->getSource());
    }
}
