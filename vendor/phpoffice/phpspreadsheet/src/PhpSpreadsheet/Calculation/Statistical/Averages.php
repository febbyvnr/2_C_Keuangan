<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\Statistical;

use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;

class Averages extends AggregateBase
{
    /**
     * AVEDEV.
     *
     * Returns the average of the absolute deviations of data points from their mean.
     * AVEDEV is a measure of the variability in a data set.
     *
     * Excel Function:
     *        AVEDEV(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
     *
     * @return float|string (string if result is an error)
     */
<<<<<<< HEAD
    public static function averageDeviations(mixed ...$args): string|float
=======
    public static function averageDeviations(...$args)
>>>>>>> main
    {
        $aArgs = Functions::flattenArrayIndexed($args);

        // Return value
        $returnValue = 0.0;

        $aMean = self::average(...$args);
        if ($aMean === ExcelError::DIV0()) {
            return ExcelError::NAN();
        } elseif ($aMean === ExcelError::VALUE()) {
            return ExcelError::VALUE();
        }

        $aCount = 0;
        foreach ($aArgs as $k => $arg) {
            $arg = self::testAcceptedBoolean($arg, $k);
            // Is it a numeric value?
            // Strings containing numeric values are only counted if they are string literals (not cell values)
            //    and then only in MS Excel and in Open Office, not in Gnumeric
            if ((is_string($arg)) && (!is_numeric($arg)) && (!Functions::isCellValue($k))) {
                return ExcelError::VALUE();
            }
            if (self::isAcceptedCountable($arg, $k)) {
<<<<<<< HEAD
                /** @var float|int|numeric-string $arg */
                /** @var float|int|numeric-string $aMean */
=======
>>>>>>> main
                $returnValue += abs($arg - $aMean);
                ++$aCount;
            }
        }

        // Return
        if ($aCount === 0) {
            return ExcelError::DIV0();
        }

        return $returnValue / $aCount;
    }

    /**
     * AVERAGE.
     *
     * Returns the average (arithmetic mean) of the arguments
     *
     * Excel Function:
     *        AVERAGE(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
     *
<<<<<<< HEAD
     * @return float|int|string (string if result is an error)
     */
    public static function average(mixed ...$args): string|int|float
=======
     * @return float|string (string if result is an error)
     */
    public static function average(...$args)
>>>>>>> main
    {
        $returnValue = $aCount = 0;

        // Loop through arguments
        foreach (Functions::flattenArrayIndexed($args) as $k => $arg) {
            $arg = self::testAcceptedBoolean($arg, $k);
            // Is it a numeric value?
            // Strings containing numeric values are only counted if they are string literals (not cell values)
            //    and then only in MS Excel and in Open Office, not in Gnumeric
            if ((is_string($arg)) && (!is_numeric($arg)) && (!Functions::isCellValue($k))) {
                return ExcelError::VALUE();
            }
            if (self::isAcceptedCountable($arg, $k)) {
<<<<<<< HEAD
                /** @var float|int|numeric-string $arg */
=======
>>>>>>> main
                $returnValue += $arg;
                ++$aCount;
            }
        }

        // Return
        if ($aCount > 0) {
            return $returnValue / $aCount;
        }

        return ExcelError::DIV0();
    }

    /**
     * AVERAGEA.
     *
     * Returns the average of its arguments, including numbers, text, and logical values
     *
     * Excel Function:
     *        AVERAGEA(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
     *
<<<<<<< HEAD
     * @return float|int|string (string if result is an error)
     */
    public static function averageA(mixed ...$args): string|int|float
=======
     * @return float|string (string if result is an error)
     */
    public static function averageA(...$args)
>>>>>>> main
    {
        $returnValue = null;

        $aCount = 0;
        // Loop through arguments
        foreach (Functions::flattenArrayIndexed($args) as $k => $arg) {
            if (is_numeric($arg)) {
                // do nothing
            } elseif (is_bool($arg)) {
                $arg = (int) $arg;
            } elseif (!Functions::isMatrixValue($k)) {
                $arg = 0;
            } else {
                return ExcelError::VALUE();
            }
            $returnValue += $arg;
            ++$aCount;
        }

        if ($aCount > 0) {
            return $returnValue / $aCount;
        }

        return ExcelError::DIV0();
    }

    /**
     * MEDIAN.
     *
     * Returns the median of the given numbers. The median is the number in the middle of a set of numbers.
     *
     * Excel Function:
     *        MEDIAN(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
     *
     * @return float|string The result, or a string containing an error
     */
<<<<<<< HEAD
    public static function median(mixed ...$args): float|string
=======
    public static function median(...$args)
>>>>>>> main
    {
        $aArgs = Functions::flattenArray($args);

        $returnValue = ExcelError::NAN();

<<<<<<< HEAD
        /** @var array<float|int> */
=======
>>>>>>> main
        $aArgs = self::filterArguments($aArgs);
        $valueCount = count($aArgs);
        if ($valueCount > 0) {
            sort($aArgs, SORT_NUMERIC);
            $valueCount = $valueCount / 2;
            if ($valueCount == floor($valueCount)) {
<<<<<<< HEAD
                $returnValue = ($aArgs[$valueCount--] + $aArgs[$valueCount]) / 2; //* @phpstan-ignore-line
            } else {
                $valueCount = (int) floor($valueCount);
=======
                $returnValue = ($aArgs[$valueCount--] + $aArgs[$valueCount]) / 2;
            } else {
                $valueCount = floor($valueCount);
>>>>>>> main
                $returnValue = $aArgs[$valueCount];
            }
        }

        return $returnValue;
    }

    /**
     * MODE.
     *
     * Returns the most frequently occurring, or repetitive, value in an array or range of data
     *
     * Excel Function:
     *        MODE(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
     *
     * @return float|string The result, or a string containing an error
     */
<<<<<<< HEAD
    public static function mode(mixed ...$args): float|string
=======
    public static function mode(...$args)
>>>>>>> main
    {
        $returnValue = ExcelError::NA();

        // Loop through arguments
        $aArgs = Functions::flattenArray($args);
        $aArgs = self::filterArguments($aArgs);

        if (!empty($aArgs)) {
            return self::modeCalc($aArgs);
        }

        return $returnValue;
    }

<<<<<<< HEAD
    /**
     * @param mixed[] $args
     *
     * @return mixed[]
     */
=======
>>>>>>> main
    protected static function filterArguments(array $args): array
    {
        return array_filter(
            $args,
<<<<<<< HEAD
            function ($value): bool {
                // Is it a numeric value?
                return is_numeric($value) && (!is_string($value));
=======
            function ($value) {
                // Is it a numeric value?
                return  is_numeric($value) && (!is_string($value));
>>>>>>> main
            }
        );
    }

    /**
     * Special variant of array_count_values that isn't limited to strings and integers,
     * but can work with floating point numbers as values.
     *
<<<<<<< HEAD
     * @param mixed[] $data
     */
    private static function modeCalc(array $data): float|string
=======
     * @return float|string
     */
    private static function modeCalc(array $data)
>>>>>>> main
    {
        $frequencyArray = [];
        $index = 0;
        $maxfreq = 0;
        $maxfreqkey = '';
        $maxfreqdatum = '';
        foreach ($data as $datum) {
<<<<<<< HEAD
            /** @var float|string $datum */
            $found = false;
            ++$index;
            foreach ($frequencyArray as $key => $value) {
                /** @var string[] $value */
=======
            $found = false;
            ++$index;
            foreach ($frequencyArray as $key => $value) {
>>>>>>> main
                if ((string) $value['value'] == (string) $datum) {
                    ++$frequencyArray[$key]['frequency'];
                    $freq = $frequencyArray[$key]['frequency'];
                    if ($freq > $maxfreq) {
                        $maxfreq = $freq;
                        $maxfreqkey = $key;
                        $maxfreqdatum = $datum;
                    } elseif ($freq == $maxfreq) {
<<<<<<< HEAD
                        if ($frequencyArray[$key]['index'] < $frequencyArray[$maxfreqkey]['index']) { //* @phpstan-ignore-line
=======
                        if ($frequencyArray[$key]['index'] < $frequencyArray[$maxfreqkey]['index']) {
>>>>>>> main
                            $maxfreqkey = $key;
                            $maxfreqdatum = $datum;
                        }
                    }
                    $found = true;

                    break;
                }
            }

            if ($found === false) {
                $frequencyArray[] = [
                    'value' => $datum,
                    'frequency' => 1,
                    'index' => $index,
                ];
            }
        }

        if ($maxfreq <= 1) {
            return ExcelError::NA();
        }

        return $maxfreqdatum;
    }
}
