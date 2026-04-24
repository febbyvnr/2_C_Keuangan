<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\MathTrig;

use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ErrorValue;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
<<<<<<< HEAD
=======
use PhpOffice\PhpSpreadsheet\Calculation\Information\Value;
>>>>>>> main

class Sum
{
    /**
     * SUM, ignoring non-numeric non-error strings. This is eventually used by SUMIF.
     *
     * SUM computes the sum of all the values and cells referenced in the argument list.
     *
     * Excel Function:
     *        SUM(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
<<<<<<< HEAD
     */
    public static function sumIgnoringStrings(mixed ...$args): float|int|string
=======
     *
     * @return float|string
     */
    public static function sumIgnoringStrings(...$args)
>>>>>>> main
    {
        $returnValue = 0;

        // Loop through the arguments
        foreach (Functions::flattenArray($args) as $arg) {
            // Is it a numeric value?
            if (is_numeric($arg)) {
                $returnValue += $arg;
            } elseif (ErrorValue::isError($arg)) {
<<<<<<< HEAD
                /** @var string $arg */
=======
>>>>>>> main
                return $arg;
            }
        }

        return $returnValue;
    }

    /**
     * SUM, returning error for non-numeric strings. This is used by Excel SUM function.
     *
     * SUM computes the sum of all the values and cells referenced in the argument list.
     *
     * Excel Function:
     *        SUM(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
     *
<<<<<<< HEAD
     * @return array<mixed>|float|int|string
     */
    public static function sumErroringStrings(mixed ...$args): float|int|string|array
=======
     * @return float|string
     */
    public static function sumErroringStrings(...$args)
>>>>>>> main
    {
        $returnValue = 0;
        // Loop through the arguments
        $aArgs = Functions::flattenArrayIndexed($args);
        foreach ($aArgs as $k => $arg) {
            // Is it a numeric value?
<<<<<<< HEAD
            if (is_numeric($arg)) {
                $returnValue += $arg;
            } elseif (is_bool($arg)) {
                $returnValue += (int) $arg;
            } elseif (ErrorValue::isError($arg, true)) {
                /** @var string $arg */
=======
            if (is_numeric($arg) || empty($arg)) {
                if (is_string($arg)) {
                    $arg = (int) $arg;
                }
                $returnValue += $arg;
            } elseif (is_bool($arg)) {
                $returnValue += (int) $arg;
            } elseif (ErrorValue::isError($arg)) {
>>>>>>> main
                return $arg;
            } elseif ($arg !== null && !Functions::isCellValue($k)) {
                // ignore non-numerics from cell, but fail as literals (except null)
                return ExcelError::VALUE();
            }
        }

        return $returnValue;
    }

    /**
     * SUMPRODUCT.
     *
     * Excel Function:
     *        SUMPRODUCT(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
     *
<<<<<<< HEAD
     * @return float|int|string The result, or a string containing an error
     */
    public static function product(mixed ...$args): string|int|float
=======
     * @return float|string The result, or a string containing an error
     */
    public static function product(...$args)
>>>>>>> main
    {
        $arrayList = $args;

        $wrkArray = Functions::flattenArray(array_shift($arrayList));
        $wrkCellCount = count($wrkArray);

        for ($i = 0; $i < $wrkCellCount; ++$i) {
            if ((!is_numeric($wrkArray[$i])) || (is_string($wrkArray[$i]))) {
                $wrkArray[$i] = 0;
            }
        }

        foreach ($arrayList as $matrixData) {
            $array2 = Functions::flattenArray($matrixData);
            $count = count($array2);
            if ($wrkCellCount != $count) {
                return ExcelError::VALUE();
            }

            foreach ($array2 as $i => $val) {
                if ((!is_numeric($val)) || (is_string($val))) {
                    $val = 0;
                }
<<<<<<< HEAD
                /** @var array<float|int> $wrkArray */
=======
>>>>>>> main
                $wrkArray[$i] *= $val;
            }
        }

<<<<<<< HEAD
        /** @var array<float|int> $wrkArray */
=======
>>>>>>> main
        return array_sum($wrkArray);
    }
}
