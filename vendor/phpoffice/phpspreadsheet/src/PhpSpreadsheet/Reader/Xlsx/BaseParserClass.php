<?php

namespace PhpOffice\PhpSpreadsheet\Reader\Xlsx;

<<<<<<< HEAD
use Stringable;

class BaseParserClass
{
    protected static function boolean(mixed $value): bool
    {
        if (is_object($value)) {
            $value = ($value instanceof Stringable) ? ((string) $value) : 'true';
=======
class BaseParserClass
{
    /**
     * @param mixed $value
     */
    protected static function boolean($value): bool
    {
        if (is_object($value)) {
            $value = (string) $value; // @phpstan-ignore-line
>>>>>>> main
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return $value === 'true' || $value === 'TRUE';
    }
}
