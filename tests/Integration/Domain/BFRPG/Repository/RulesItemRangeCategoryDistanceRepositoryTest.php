<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\BFRPG\Repository;

use App\Domain\BFRPG\Repository\RulesItemRangeCategoryDistanceRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesItemRangeCategoryDistanceRepository::class)]
final class RulesItemRangeCategoryDistanceRepositoryTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(RulesItemRangeCategoryDistanceRepository::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            RulesItemRangeCategoryDistanceRepository::class,
            $this->getContainer()->get(RulesItemRangeCategoryDistanceRepository::class),
            'The service must resolve to a valid RulesItemRangeCategoryDistanceRepository instance.'
        );
    }
}
