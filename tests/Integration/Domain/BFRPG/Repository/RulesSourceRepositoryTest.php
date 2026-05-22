<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\BFRPG\Repository;

use App\Domain\BFRPG\Repository\RuleSourceRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('bfrpg')]
#[CoversClass(RuleSourceRepository::class)]
final class RulesSourceRepositoryTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(RuleSourceRepository::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            RuleSourceRepository::class,
            $this->getContainer()->get(RuleSourceRepository::class),
            'The service must resolve to a valid RuleSourceRepository instance.'
        );
    }
}
