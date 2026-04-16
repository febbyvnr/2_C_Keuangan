<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities;

use PhpOffice\PhpSpreadsheet\Calculation\Exception;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\FinancialValidations;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;

class SecurityValidations extends FinancialValidations
{
<<<<<<< HEAD
    public static function validateIssueDate(mixed $issue): float
=======
    /**
     * @param mixed $issue
     */
    public static function validateIssueDate($issue): float
>>>>>>> main
    {
        return self::validateDate($issue);
    }

<<<<<<< HEAD
    public static function validateSecurityPeriod(mixed $settlement, mixed $maturity): void
=======
    /**
     * @param mixed $settlement
     * @param mixed $maturity
     */
    public static function validateSecurityPeriod($settlement, $maturity): void
>>>>>>> main
    {
        if ($settlement >= $maturity) {
            throw new Exception(ExcelError::NAN());
        }
    }

<<<<<<< HEAD
    public static function validateRedemption(mixed $redemption): float
=======
    /**
     * @param mixed $redemption
     */
    public static function validateRedemption($redemption): float
>>>>>>> main
    {
        $redemption = self::validateFloat($redemption);
        if ($redemption <= 0.0) {
            throw new Exception(ExcelError::NAN());
        }

        return $redemption;
    }
}
