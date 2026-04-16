<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\Engineering;

use PhpOffice\PhpSpreadsheet\Calculation\Exception;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;

class EngineeringValidations
{
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
}
