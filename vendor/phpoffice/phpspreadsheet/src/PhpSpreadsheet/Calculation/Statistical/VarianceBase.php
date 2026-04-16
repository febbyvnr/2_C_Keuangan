<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\Statistical;

use PhpOffice\PhpSpreadsheet\Calculation\Functions;

abstract class VarianceBase
{
<<<<<<< HEAD
    protected static function datatypeAdjustmentAllowStrings(int|float|string|bool $value): int|float
=======
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected static function datatypeAdjustmentAllowStrings($value)
>>>>>>> main
    {
        if (is_bool($value)) {
            return (int) $value;
        } elseif (is_string($value)) {
            return 0;
        }

        return $value;
    }

<<<<<<< HEAD
    protected static function datatypeAdjustmentBooleans(mixed $value): mixed
=======
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected static function datatypeAdjustmentBooleans($value)
>>>>>>> main
    {
        if (is_bool($value) && (Functions::getCompatibilityMode() == Functions::COMPATIBILITY_OPENOFFICE)) {
            return (int) $value;
        }

        return $value;
    }
}
