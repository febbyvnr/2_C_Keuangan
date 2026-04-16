<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\Financial\CashFlow;

use PhpOffice\PhpSpreadsheet\Calculation\Exception;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\Constants as FinancialConstants;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\FinancialValidations;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;

class CashFlowValidations extends FinancialValidations
{
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

        return $rate;
    }

<<<<<<< HEAD
    public static function validatePeriodType(mixed $type): int
    {
        $rate = self::validateInt($type);
        if (
            $type !== FinancialConstants::PAYMENT_END_OF_PERIOD
            && $type !== FinancialConstants::PAYMENT_BEGINNING_OF_PERIOD
=======
    /**
     * @param mixed $type
     */
    public static function validatePeriodType($type): int
    {
        $rate = self::validateInt($type);
        if (
            $type !== FinancialConstants::PAYMENT_END_OF_PERIOD &&
            $type !== FinancialConstants::PAYMENT_BEGINNING_OF_PERIOD
>>>>>>> main
        ) {
            throw new Exception(ExcelError::NAN());
        }

        return $rate;
    }

<<<<<<< HEAD
    public static function validatePresentValue(mixed $presentValue): float
=======
    /**
     * @param mixed $presentValue
     */
    public static function validatePresentValue($presentValue): float
>>>>>>> main
    {
        return self::validateFloat($presentValue);
    }

<<<<<<< HEAD
    public static function validateFutureValue(mixed $futureValue): float
=======
    /**
     * @param mixed $futureValue
     */
    public static function validateFutureValue($futureValue): float
>>>>>>> main
    {
        return self::validateFloat($futureValue);
    }
}
