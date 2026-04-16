<?php

namespace PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard;

<<<<<<< HEAD
use Stringable;

abstract class DateTimeWizard implements Stringable, Wizard
{
    protected const NO_ESCAPING_NEEDED = "$+-/():!^&'~{}<>= ";

    /**
     * @param array<?string> $separators
     *
     * @return array<?string>
     */
    protected function padSeparatorArray(array $separators, int $count): array
    {
        $lastSeparator = (string) array_pop($separators);
=======
abstract class DateTimeWizard implements Wizard
{
    protected const NO_ESCAPING_NEEDED = "$+-/():!^&'~{}<>= ";

    protected function padSeparatorArray(array $separators, int $count): array
    {
        $lastSeparator = array_pop($separators);
>>>>>>> main

        return $separators + array_fill(0, $count, $lastSeparator);
    }

    protected function escapeSingleCharacter(string $value): string
    {
<<<<<<< HEAD
        if (str_contains(self::NO_ESCAPING_NEEDED, $value)) {
=======
        if (strpos(self::NO_ESCAPING_NEEDED, $value) !== false) {
>>>>>>> main
            return $value;
        }

        return "\\{$value}";
    }

    protected function wrapLiteral(string $value): string
    {
        if (mb_strlen($value, 'UTF-8') === 1) {
            return $this->escapeSingleCharacter($value);
        }

        // Wrap any other string literals in quotes, so that they're clearly defined as string literals
        return '"' . str_replace('"', '""', $value) . '"';
    }

    protected function intersperse(string $formatBlock, ?string $separator): string
    {
        return "{$formatBlock}{$separator}";
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
