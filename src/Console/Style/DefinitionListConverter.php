<?php

namespace App\Console\Style;

use ArrayObject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DefinitionListConverter
{
    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(protected NormalizerInterface $normalizer)
    {
    }

    /**
     * @param mixed $subject
     * @param string $nestedSeparator
     * @return array
     * @throws ExceptionInterface
     */
    public function convert(mixed $subject, string $nestedSeparator = '.'): array
    {
        $normalizedSubject = $this->normalizer->normalize($subject);

        if ($normalizedSubject === null || is_scalar($normalizedSubject)) {
            return [$normalizedSubject];
        }

        $flattenedNormalizedSubject = $this->flattenNormalized($normalizedSubject, $nestedSeparator);

        $definitionList = [];

        foreach ($flattenedNormalizedSubject as $key => $value) {
            $definitionList[] = [$key => $value];
        }

        return $definitionList;
    }

    /**
     * @param array|ArrayObject $normalized
     * @param string $nestedSeparator
     * @param string $keyPrefix
     * @return array
     */
    protected function flattenNormalized(
        array|ArrayObject $normalized,
        string $nestedSeparator,
        string $keyPrefix = ''
    ): array
    {
        $flattened = [];

        foreach ($normalized as $key => $value) {
            $flattenedKey = sprintf(
                '%s%s',
                empty($keyPrefix) ? '' : sprintf('%s%s', $keyPrefix, $nestedSeparator),
                $key
            );

            if (is_array($value) || $value instanceof ArrayObject) {
                $flattened = [...$flattened, ...$this->flattenNormalized($value, $nestedSeparator, $flattenedKey)];
                continue;
            }

            $flattened[$flattenedKey] = $value;
        }

        return $flattened;
    }
}
