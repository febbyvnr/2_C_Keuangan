<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\LookupRef;

use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;

class Filter
{
<<<<<<< HEAD
    public static function filter(mixed $lookupArray, mixed $matchArray, mixed $ifEmpty = null): mixed
    {
        if (!is_array($lookupArray)) {
            return ExcelError::VALUE();
        }
        /** @var mixed[] $lookupArray */
=======
    /**
     * @param mixed $lookupArray
     * @param mixed $matchArray
     * @param mixed $ifEmpty
     *
     * @return mixed
     */
    public static function filter($lookupArray, $matchArray, $ifEmpty = null)
    {
>>>>>>> main
        if (!is_array($matchArray)) {
            return ExcelError::VALUE();
        }

        $matchArray = self::enumerateArrayKeys($matchArray);

        $result = (Matrix::isColumnVector($matchArray))
            ? self::filterByRow($lookupArray, $matchArray)
            : self::filterByColumn($lookupArray, $matchArray);

        if (empty($result)) {
            return $ifEmpty ?? ExcelError::CALC();
        }
<<<<<<< HEAD
        /** @var callable(mixed): mixed */
        $func = 'array_values';

        return array_values(array_map($func, $result));
    }

    /**
     * @param mixed[] $sortArray
     *
     * @return mixed[]
     */
=======

        return array_values(array_map('array_values', $result));
    }

>>>>>>> main
    private static function enumerateArrayKeys(array $sortArray): array
    {
        array_walk(
            $sortArray,
            function (&$columns): void {
                if (is_array($columns)) {
                    $columns = array_values($columns);
                }
            }
        );

        return array_values($sortArray);
    }

<<<<<<< HEAD
    /**
     * @param mixed[] $lookupArray
     * @param mixed[] $matchArray
     *
     * @return mixed[]
     */
    private static function filterByRow(array $lookupArray, array $matchArray): array
    {
        $matchArray = array_values(array_column($matchArray, 0)); // @phpstan-ignore-line

        return array_filter(
            array_values($lookupArray),
            fn ($index): bool => (bool) ($matchArray[$index] ?? null),
=======
    private static function filterByRow(array $lookupArray, array $matchArray): array
    {
        $matchArray = array_values(array_column($matchArray, 0));

        return array_filter(
            array_values($lookupArray),
            function ($index) use ($matchArray): bool {
                return (bool) $matchArray[$index];
            },
>>>>>>> main
            ARRAY_FILTER_USE_KEY
        );
    }

<<<<<<< HEAD
    /**
     * @param mixed[] $lookupArray
     * @param mixed[] $matchArray
     *
     * @return mixed[]
     */
=======
>>>>>>> main
    private static function filterByColumn(array $lookupArray, array $matchArray): array
    {
        $lookupArray = Matrix::transpose($lookupArray);

        if (count($matchArray) === 1) {
            $matchArray = array_pop($matchArray);
        }
<<<<<<< HEAD
        /** @var mixed[] $matchArray */
=======

>>>>>>> main
        array_walk(
            $matchArray,
            function (&$value): void {
                $value = [$value];
            }
        );

        $result = self::filterByRow($lookupArray, $matchArray);

        return Matrix::transpose($result);
    }
}
