<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\Financial;

use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel;
use PhpOffice\PhpSpreadsheet\Calculation\Exception;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\Constants as FinancialConstants;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;

class FinancialValidations
{
<<<<<<< HEAD
    public static function validateDate(mixed $date): float
=======
    /**
     * @param mixed $date
     */
    public static function validateDate($date): float
>>>>>>> main
    {
        return DateTimeExcel\Helpers::getDateValue($date);
    }

<<<<<<< HEAD
    public static function validateSettlementDate(mixed $settlement): float
=======
    /**
     * @param mixed $settlement
     */
    public static function validateSettlementDate($settlement): float
>>>>>>> main
    {
        return self::validateDate($settlement);
    }

<<<<<<< HEAD
    public static function validateMaturityDate(mixed $maturity): float
=======
    /**
     * @param mixed $maturity
     */
    public static function validateMaturityDate($maturity): float
>>>>>>> main
    {
        return self::validateDate($maturity);
    }

<<<<<<< HEAD
    public static function validateFloat(mixed $value): float
=======
    /**
     * @param mixed $value
     */
    public static function validateFloat($value): float
>>>>>>> main
    {
        if (!is_numeric($value)) {
            throw new Exception(ExcelError::VALUE());
        }

        return (float) $value;
    }

<<<<<<< HEAD
    public static function validateInt(mixed $value): int
=======
    /**
     * @param mixed $value
     */
    public static function validateInt($value): int
>>>>>>> main
    {
        if (!is_numeric($value)) {
            throw new Exception(ExcelError::VALUE());
        }

        return (int) floor((float) $value);
    }

<<<<<<< HEAD
    public static function validateRate(mixed $rate): float
=======
    /**
     * @param mixed $rate
     */
    public static function validateRate($rate): float
>>>>>>> main
    {
        $rate = self::validateFloat($rate);
        if ($rate < 0.0) {
            throw new Exception(ExcelError::NAN());
        }

        return $rate;
    }

<<<<<<< HEAD
    public static function validateFrequency(mixed $frequency): int
    {
        $frequency = self::validateInt($frequency);
        if (
            ($frequency !== FinancialConstants::FREQUENCY_ANNUAL)
            && ($frequency !== FinancialConstants::FREQUENCY_SEMI_ANNUAL)
            && ($frequency !== FinancialConstants::FREQUENCY_QUARTERLY)
=======
    /**
     * @param mixed $frequency
     */
    public static function validateFrequency($frequency): int
    {
        $frequency = self::validateInt($frequency);
        if (
            ($frequency !== FinancialConstants::FREQUENCY_ANNUAL) &&
            ($frequency !== FinancialConstants::FREQUENCY_SEMI_ANNUAL) &&
            ($frequency !== FinancialConstants::FREQUENCY_QUARTERLY)
>>>>>>> main
        ) {
            throw new Exception(ExcelError::NAN());
        }

        return $frequency;
    }

<<<<<<< HEAD
    public static function validateBasis(mixed $basis): int
=======
    /**
     * @param mixed $basis
     */
    public static function validateBasis($basis): int
>>>>>>> main
    {
        if (!is_numeric($basis)) {
            throw new Exception(ExcelError::VALUE());
        }

        $basis = (int) $basis;
        if (($basis < 0) || ($basis > 4)) {
            throw new Exception(ExcelError::NAN());
        }

        return $basis;
    }

<<<<<<< HEAD
    public static function validatePrice(mixed $price): float
=======
    /**
     * @param mixed $price
     */
    public static function validatePrice($price): float
>>>>>>> main
    {
        $price = self::validateFloat($price);
        if ($price < 0.0) {
            throw new Exception(ExcelError::NAN());
        }

        return $price;
    }

<<<<<<< HEAD
    public static function validateParValue(mixed $parValue): float
=======
    /**
     * @param mixed $parValue
     */
    public static function validateParValue($parValue): float
>>>>>>> main
    {
        $parValue = self::validateFloat($parValue);
        if ($parValue < 0.0) {
            throw new Exception(ExcelError::NAN());
        }

        return $parValue;
    }

<<<<<<< HEAD
    public static function validateYield(mixed $yield): float
=======
    /**
     * @param mixed $yield
     */
    public static function validateYield($yield): float
>>>>>>> main
    {
        $yield = self::validateFloat($yield);
        if ($yield < 0.0) {
            throw new Exception(ExcelError::NAN());
        }

        return $yield;
    }

<<<<<<< HEAD
    public static function validateDiscount(mixed $discount): float
=======
    /**
     * @param mixed $discount
     */
    public static function validateDiscount($discount): float
>>>>>>> main
    {
        $discount = self::validateFloat($discount);
        if ($discount <= 0.0) {
            throw new Exception(ExcelError::NAN());
        }

        return $discount;
    }
}
