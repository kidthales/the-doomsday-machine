<?php

declare(strict_types=1);

namespace App\Tests\Console\Style;

use App\Console\Style\DefinitionListConverter;
use App\Entity\Discord\Api\Dto\Team;
use App\Entity\Discord\Api\Dto\TeamMember;
use App\Entity\Discord\Api\Dto\User;
use App\Entity\Discord\Api\Enumeration\MembershipState;
use App\Entity\Discord\Api\Enumeration\TeamMemberRole;
use ArrayObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @covers \App\Console\Style\DefinitionListConverter
 */
final class DefinitionListConverterTest extends KernelTestCase
{
    /**
     * @return array
     */
    public static function provider_convert(): array
    {
        return [
            [null, [null], null],
            [null, [null], ','],
            [true, [true], null],
            [true, [true], ','],
            ['test', ['test'], null],
            ['test', ['test'], ','],
            [12, [12], null],
            [12, [12], ','],
            [12.7, [12.7], null],
            [12.7, [12.7], ','],
            [[], [], null],
            [[], [], ','],
            [
                ['test-key-1' => 'test-value-1'],
                [['test-key-1' => 'test-value-1']],
                null
            ],
            [
                ['test-key-1' => 'test-value-1'],
                [['test-key-1' => 'test-value-1']],
                ','
            ],
            [
                ['test-key-1' => 'test-value-1', 'test-key-2' => 'test-value-2'],
                [['test-key-1' => 'test-value-1'], ['test-key-2' => 'test-value-2']],
                null
            ],
            [
                ['test-key-1' => 'test-value-1', 'test-key-2' => 'test-value-2'],
                [['test-key-1' => 'test-value-1'], ['test-key-2' => 'test-value-2']],
                ','
            ],
            [
                ['test-key-1' => [], 'test-key-2' => 'test-value-2'],
                [['test-key-2' => 'test-value-2']],
                null
            ],
            [
                ['test-key-1' => [], 'test-key-2' => 'test-value-2'],
                [['test-key-2' => 'test-value-2']],
                ','
            ],
            [
                ['test-key-1' => ['nested-test-key' => 'test-value-1'], 'test-key-2' => 'test-value-2'],
                [['test-key-1.nested-test-key' => 'test-value-1'], ['test-key-2' => 'test-value-2']],
                null
            ],
            [
                ['test-key-1' => ['nested-test-key' => 'test-value-1'], 'test-key-2' => 'test-value-2'],
                [['test-key-1,nested-test-key' => 'test-value-1'], ['test-key-2' => 'test-value-2']],
                ','
            ],
            [
                new Team(
                    icon: 'test-icon',
                    id: 'test-id',
                    members: [
                        new TeamMember(
                            membership_state: MembershipState::ACCEPTED,
                            team_id: 'test-team-id',
                            user: new User(
                                id: 'test-user-id',
                                username: 'test-username',
                                discriminator: 'test-discriminator',
                                global_name: null,
                                avatar: null
                            ),
                            role: TeamMemberRole::developer
                        )
                    ],
                    name: 'test-name',
                    owner_user_id: 'test-owner-user-id'
                ),
                [
                    ['icon' => 'test-icon'],
                    ['id' => 'test-id'],
                    ['members.0.membership_state' => MembershipState::ACCEPTED->value],
                    ['members.0.team_id' => 'test-team-id'],
                    ['members.0.user.id' => 'test-user-id'],
                    ['members.0.user.username' => 'test-username'],
                    ['members.0.user.discriminator' => 'test-discriminator'],
                    ['members.0.user.global_name' => null],
                    ['members.0.user.avatar' => null],
                    ['members.0.user.banner' => null],
                    ['members.0.user.accent_color' => null],
                    ['members.0.user.email' => null],
                    ['members.0.user.avatar_decoration_data' => null],
                    ['members.0.role' => 'developer'],
                    ['name' => 'test-name'],
                    ['owner_user_id' => 'test-owner-user-id']
                ],
                null
            ],
            [
                new NormalizesAsEmptyArrayObject(),
                [],
                null
            ],
            [
                new NormalizesAsArrayObject(id: 'test-id'),
                [['id' => 'test-id'], ['nested' => null]],
                null
            ],
            [
                new NormalizesAsArrayObject(id: 'test-id', nested: new NormalizesAsArrayObject(id: 'test-id')),
                [['id' => 'test-id'], ['nested.id' => 'test-id'], ['nested.nested' => null]],
                null
            ],
            [
                new NormalizesAsArrayObject(
                    id: 'test-id',
                    nested: new NormalizesAsArrayObject(
                        id: 'test-id',
                        nested: new NormalizesAsArrayObject(id: 'test-id')
                    ),
                ),
                [
                    ['id' => 'test-id'],
                    ['nested.id' => 'test-id'],
                    ['nested.nested.id' => 'test-id'],
                    ['nested.nested.nested' => null]
                ],
                null
            ],
            [
                new NormalizesAsArrayObject(
                    id: 'test-id',
                    nested: new NormalizesAsArrayObject(
                        id: 'test-id',
                        nested: new NormalizesAsArrayObject(id: 'test-id')
                    ),
                ),
                [
                    ['id' => 'test-id'],
                    ['nested,id' => 'test-id'],
                    ['nested,nested,id' => 'test-id'],
                    ['nested,nested,nested' => null]
                ],
                ','
            ]
        ];
    }

    /**
     * @param mixed $subject
     * @param array $expected
     * @param string|null $separator
     * @return void
     * @dataProvider provider_convert
     */
    public function test_convert(mixed $subject, array $expected, string|null $separator): void
    {
        self::bootKernel();

        $converter = self::getContainer()->get(DefinitionListConverter::class);

        if ($separator === null) {
            $actual = $converter->convert($subject);
        } else {
            $actual = $converter->convert($subject, $separator);
        }

        self::assertSame(count($expected), count($actual));

        foreach ($expected as $key => $value) {
            self::assertSame($value, $actual[$key]);
        }
    }
}

class NormalizesAsEmptyArrayObject implements NormalizableInterface
{
    public function normalize(
        NormalizerInterface $normalizer,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|null
    {
        return new ArrayObject();
    }
}

class NormalizesAsArrayObject extends NormalizesAsEmptyArrayObject
{
    /**
     * @param string $id
     * @param NormalizesAsArrayObject|null $nested
     */
    public function __construct(public string $id, public ?NormalizesAsArrayObject $nested = null)
    {
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return array|string|int|float|bool|ArrayObject|null
     * @throws ExceptionInterface
     */
    public function normalize(
        NormalizerInterface $normalizer,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|null
    {
        return new ArrayObject([
            'id' => $this->id,
            'nested' => $normalizer->normalize($this->nested, $format, $context)
        ]);
    }
}
