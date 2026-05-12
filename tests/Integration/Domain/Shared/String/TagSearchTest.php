<?php

declare(strict_types=1);

namespace App\Tests\Integration\Domain\Shared\String;

use App\Domain\Shared\String\TagSearch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author doomsday_coder
 * @author Tristan Bonsor <kidthales@agogpixel.com>
 */
#[Group('shared')]
#[CoversClass(TagSearch::class)]
final class TagSearchTest extends KernelTestCase
{
    #[Test]
    public function it_is_provided_by_the_service_container(): void
    {
        $this->bootKernel();

        $this->assertTrue(
            $this->getContainer()->has(TagSearch::class),
            'The autowired service must be registered in the container.'
        );

        $this->assertInstanceOf(
            TagSearch::class,
            $this->getContainer()->get(TagSearch::class),
            'The service must resolve to a valid TagSearch instance.'
        );
    }
}
