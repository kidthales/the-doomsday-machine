<?php

declare(strict_types=1);

namespace App\Tests\TestHelper;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractSerializableSubjectTestCase extends KernelTestCase
{
    /**
     * @param mixed $expected
     * @param mixed $actual
     * @return void
     */
    public static function assertDeepSame(mixed $expected, mixed $actual): void
    {
        throw new \LogicException('Must override this method in child test case');
    }

    /**
     * @return SerializerInterface
     */
    protected static function getSerializer(): SerializerInterface
    {
        return self::getContainer()->get(SerializerInterface::class);
    }

    /**
     * @param mixed $subject
     * @param string $expected
     * @return void
     */
    protected static function testSerialization(mixed $subject, string $expected): void
    {
        self::bootKernel();

        $actual = self::getSerializer()->serialize($subject, 'json');

        self::assertSame($expected, $actual);
    }

    /**
     * @param string $subject
     * @param mixed $expected
     * @param string $type
     * @return void
     */
    protected static function testDeserialization(string $subject, mixed $expected, string $type): void
    {
        self::bootKernel();

        $actual = self::getSerializer()->deserialize($subject, $type, 'json');

        self::assertInstanceOf($type, $actual);
        static::assertDeepSame($expected, $actual);
    }
}
