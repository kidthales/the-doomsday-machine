<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\BFRPG\Repository;

use App\Domain\BFRPG\Repository\RulesItemRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('bfrpg')]
#[CoversClass(RulesItemRepository::class)]
final class RulesItemRepositoryTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(RulesItemRepository::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            RulesItemRepository::class,
            $this->getContainer()->get(RulesItemRepository::class),
            'The service must resolve to a valid RulesItemRepository instance.'
        );
    }
}
