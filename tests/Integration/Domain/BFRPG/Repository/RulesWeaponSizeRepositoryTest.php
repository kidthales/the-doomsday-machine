<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\BFRPG\Repository;

use App\Domain\BFRPG\Repository\RulesWeaponSizeRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesWeaponSizeRepository::class)]
final class RulesWeaponSizeRepositoryTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(RulesWeaponSizeRepository::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            RulesWeaponSizeRepository::class,
            $this->getContainer()->get(RulesWeaponSizeRepository::class),
            'The service must resolve to a valid RulesWeaponSizeRepository instance.'
        );
    }
}
