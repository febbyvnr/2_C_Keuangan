<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\Statistical;

class StandardDeviations
{
    /**
     * STDEV.
     *
     * Estimates standard deviation based on a sample. The standard deviation is a measure of how
     *        widely values are dispersed from the average value (the mean).
     *
     * Excel Function:
     *        STDEV(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
     *
     * @return float|string The result, or a string containing an error
     */
<<<<<<< HEAD
    public static function STDEV(mixed ...$args)
=======
    public static function STDEV(...$args)
>>>>>>> main
    {
        $result = Variances::VAR(...$args);
        if (!is_numeric($result)) {
            return $result;
        }

        return sqrt((float) $result);
    }

    /**
     * STDEVA.
     *
     * Estimates standard deviation based on a sample, including numbers, text, and logical values
     *
     * Excel Function:
     *        STDEVA(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
<<<<<<< HEAD
     */
    public static function STDEVA(mixed ...$args): float|string
=======
     *
     * @return float|string
     */
    public static function STDEVA(...$args)
>>>>>>> main
    {
        $result = Variances::VARA(...$args);
        if (!is_numeric($result)) {
            return $result;
        }

        return sqrt((float) $result);
    }

    /**
     * STDEVP.
     *
     * Calculates standard deviation based on the entire population
     *
     * Excel Function:
     *        STDEVP(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
<<<<<<< HEAD
     */
    public static function STDEVP(mixed ...$args): float|string
=======
     *
     * @return float|string
     */
    public static function STDEVP(...$args)
>>>>>>> main
    {
        $result = Variances::VARP(...$args);
        if (!is_numeric($result)) {
            return $result;
        }

        return sqrt((float) $result);
    }

    /**
     * STDEVPA.
     *
     * Calculates standard deviation based on the entire population, including numbers, text, and logical values
     *
     * Excel Function:
     *        STDEVPA(value1[,value2[, ...]])
     *
     * @param mixed ...$args Data values
<<<<<<< HEAD
     */
    public static function STDEVPA(mixed ...$args): float|string
=======
     *
     * @return float|string
     */
    public static function STDEVPA(...$args)
>>>>>>> main
    {
        $result = Variances::VARPA(...$args);
        if (!is_numeric($result)) {
            return $result;
        }

        return sqrt((float) $result);
    }
}
