<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\BFRPG\Repository;

use App\Domain\BFRPG\Repository\RulesWeaponCategoryRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesWeaponCategoryRepository::class)]
final class RulesWeaponCategoryRepositoryTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(RulesWeaponCategoryRepository::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            RulesWeaponCategoryRepository::class,
            $this->getContainer()->get(RulesWeaponCategoryRepository::class),
            'The service must resolve to a valid RulesWeaponCategoryRepository instance.'
        );
    }
}
