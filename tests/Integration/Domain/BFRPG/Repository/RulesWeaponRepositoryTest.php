<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\BFRPG\Repository;

use App\Domain\BFRPG\Repository\RulesWeaponRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 */
#[Group('bfrpg')]
#[CoversClass(RulesWeaponRepository::class)]
final class RulesWeaponRepositoryTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(RulesWeaponRepository::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            RulesWeaponRepository::class,
            $this->getContainer()->get(RulesWeaponRepository::class),
            'The service must resolve to a valid RulesWeaponRepository instance.'
        );
    }
}
