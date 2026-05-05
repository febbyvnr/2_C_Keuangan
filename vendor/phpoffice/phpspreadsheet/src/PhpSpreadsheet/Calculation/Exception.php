<?php

namespace PhpOffice\PhpSpreadsheet\Calculation;

use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;

class Exception extends PhpSpreadsheetException
{
    public const CALCULATION_ENGINE_PUSH_TO_STACK = 1;

    /**
     * Error handler callback.
<<<<<<< HEAD
     */
    public static function errorHandlerCallback(int $code, string $string, string $file, int $line): void
=======
     *
     * @param mixed $code
     * @param mixed $string
     * @param mixed $file
     * @param mixed $line
     * @param mixed $context
     */
    public static function errorHandlerCallback($code, $string, $file, $line, /** @scrutinizer ignore-unused */ $context): void
>>>>>>> main
    {
        $e = new self($string, $code);
        $e->line = $line;
        $e->file = $file;

        throw $e;
    }
}
