<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\MathTrig;

use PhpOffice\PhpSpreadsheet\Calculation\Exception;
use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;

class SumSquares
{
    /**
     * SUMSQ.
     *
     * SUMSQ returns the sum of the squares of the arguments
     *
     * Excel Function:
     *        SUMSQ(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
<<<<<<< HEAD
     */
    public static function sumSquare(mixed ...$args): string|int|float
=======
     *
     * @return float|string
     */
    public static function sumSquare(...$args)
>>>>>>> main
    {
        try {
            $returnValue = 0;

            // Loop through arguments
            foreach (Functions::flattenArray($args) as $arg) {
                $arg1 = Helpers::validateNumericNullSubstitution($arg, 0);
                $returnValue += ($arg1 * $arg1);
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $returnValue;
    }

<<<<<<< HEAD
    /**
     * @param mixed[] $array1
     * @param mixed[] $array2
     */
=======
>>>>>>> main
    private static function getCount(array $array1, array $array2): int
    {
        $count = count($array1);
        if ($count !== count($array2)) {
            throw new Exception(ExcelError::NA());
        }

        return $count;
    }

    /**
     * These functions accept only numeric arguments, not even strings which are numeric.
<<<<<<< HEAD
     */
    private static function numericNotString(mixed $item): bool
=======
     *
     * @param mixed $item
     */
    private static function numericNotString($item): bool
>>>>>>> main
    {
        return is_numeric($item) && !is_string($item);
    }

    /**
     * SUMX2MY2.
     *
     * @param mixed[] $matrixData1 Matrix #1
     * @param mixed[] $matrixData2 Matrix #2
<<<<<<< HEAD
     */
    public static function sumXSquaredMinusYSquared(array $matrixData1, array $matrixData2): string|int|float
    {
        try {
            /** @var array<float|int> */
            $array1 = Functions::flattenArray($matrixData1);
            /** @var array<float|int> */
=======
     *
     * @return float|string
     */
    public static function sumXSquaredMinusYSquared($matrixData1, $matrixData2)
    {
        try {
            $array1 = Functions::flattenArray($matrixData1);
>>>>>>> main
            $array2 = Functions::flattenArray($matrixData2);
            $count = self::getCount($array1, $array2);

            $result = 0;
            for ($i = 0; $i < $count; ++$i) {
                if (self::numericNotString($array1[$i]) && self::numericNotString($array2[$i])) {
                    $result += ($array1[$i] * $array1[$i]) - ($array2[$i] * $array2[$i]);
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $result;
    }

    /**
     * SUMX2PY2.
     *
     * @param mixed[] $matrixData1 Matrix #1
     * @param mixed[] $matrixData2 Matrix #2
<<<<<<< HEAD
     */
    public static function sumXSquaredPlusYSquared(array $matrixData1, array $matrixData2): string|int|float
    {
        try {
            /** @var array<float|int> */
            $array1 = Functions::flattenArray($matrixData1);
            /** @var array<float|int> */
=======
     *
     * @return float|string
     */
    public static function sumXSquaredPlusYSquared($matrixData1, $matrixData2)
    {
        try {
            $array1 = Functions::flattenArray($matrixData1);
>>>>>>> main
            $array2 = Functions::flattenArray($matrixData2);
            $count = self::getCount($array1, $array2);

            $result = 0;
            for ($i = 0; $i < $count; ++$i) {
                if (self::numericNotString($array1[$i]) && self::numericNotString($array2[$i])) {
                    $result += ($array1[$i] * $array1[$i]) + ($array2[$i] * $array2[$i]);
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $result;
    }

    /**
     * SUMXMY2.
     *
     * @param mixed[] $matrixData1 Matrix #1
     * @param mixed[] $matrixData2 Matrix #2
<<<<<<< HEAD
     */
    public static function sumXMinusYSquared(array $matrixData1, array $matrixData2): string|int|float
    {
        try {
            /** @var array<float|int> */
            $array1 = Functions::flattenArray($matrixData1);
            /** @var array<float|int> */
=======
     *
     * @return float|string
     */
    public static function sumXMinusYSquared($matrixData1, $matrixData2)
    {
        try {
            $array1 = Functions::flattenArray($matrixData1);
>>>>>>> main
            $array2 = Functions::flattenArray($matrixData2);
            $count = self::getCount($array1, $array2);

            $result = 0;
            for ($i = 0; $i < $count; ++$i) {
                if (self::numericNotString($array1[$i]) && self::numericNotString($array2[$i])) {
                    $result += ($array1[$i] - $array2[$i]) * ($array1[$i] - $array2[$i]);
                }
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $result;
    }
}
