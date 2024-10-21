<?php

declare(strict_types=1);

namespace App\Entity\Discord\Api\Dto;

use App\Entity\Discord\Api\Enumeration\LayoutType;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://discord.com/developers/docs/resources/poll#poll-create-request-object-poll-create-request-object-structure
 */
class PollCreateRequest implements NormalizableInterface
{
    /**
     * @param PollMedia $question
     * @param PollAnswer[] $answers
     * @param int|null $duration
     * @param bool|null $allow_multiselect
     * @param LayoutType|null $layout_type
     */
    public function __construct(
        public PollMedia   $question,
        public array       $answers,
        public ?int        $duration = null,
        public ?bool       $allow_multiselect = null,
        public ?LayoutType $layout_type = null,
    )
    {
    }

    /**
     * @param NormalizerInterface $normalizer
     * @param string|null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize(NormalizerInterface $normalizer, ?string $format = null, array $context = []): array
    {
        $data = [
            'question' => $normalizer->normalize($this->question, $format, $context),
            'answers' => $normalizer->normalize($this->answers, $format, $context),
        ];

        if ($this->duration !== null) {
            $data['duration'] = $this->duration;
        }

        if ($this->allow_multiselect !== null) {
            $data['allow_multiselect'] = $this->allow_multiselect;
        }

        if ($this->layout_type !== null) {
            $data['layout_type'] = $this->layout_type->value;
        }

        return $data;
    }
}
