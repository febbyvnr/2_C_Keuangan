<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\LookupRef;

use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Calculation\Information\ErrorValue;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;
=======
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
>>>>>>> main
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RowColumnInformation
{
    /**
     * Test if cellAddress is null or whitespace string.
     *
<<<<<<< HEAD
     * @param null|mixed[]|string $cellAddress A reference to a range of cells
=======
     * @param null|array|string $cellAddress A reference to a range of cells
>>>>>>> main
     */
    private static function cellAddressNullOrWhitespace($cellAddress): bool
    {
        return $cellAddress === null || (!is_array($cellAddress) && trim($cellAddress) === '');
    }

    private static function cellColumn(?Cell $cell): int
    {
<<<<<<< HEAD
        return ($cell !== null) ? Coordinate::columnIndexFromString($cell->getColumn()) : 1;
=======
        return ($cell !== null) ? (int) Coordinate::columnIndexFromString($cell->getColumn()) : 1;
>>>>>>> main
    }

    /**
     * COLUMN.
     *
     * Returns the column number of the given cell reference
     *     If the cell reference is a range of cells, COLUMN returns the column numbers of each column
     *        in the reference as a horizontal array.
     *     If cell reference is omitted, and the function is being called through the calculation engine,
     *        then it is assumed to be the reference of the cell in which the COLUMN function appears;
     *        otherwise this function returns 1.
     *
     * Excel Function:
     *        =COLUMN([cellAddress])
     *
<<<<<<< HEAD
     * @param null|mixed[]|string $cellAddress A reference to a range of cells for which you want the column numbers
     *
     * @return int|int[]|string
     */
    public static function COLUMN($cellAddress = null, ?Cell $cell = null): int|string|array
=======
     * @param null|array|string $cellAddress A reference to a range of cells for which you want the column numbers
     *
     * @return int|int[]
     */
    public static function COLUMN($cellAddress = null, ?Cell $cell = null)
>>>>>>> main
    {
        if (self::cellAddressNullOrWhitespace($cellAddress)) {
            return self::cellColumn($cell);
        }

        if (is_array($cellAddress)) {
            foreach ($cellAddress as $columnKey => $value) {
                $columnKey = (string) preg_replace('/[^a-z]/i', '', $columnKey);

<<<<<<< HEAD
                return Coordinate::columnIndexFromString($columnKey);
=======
                return (int) Coordinate::columnIndexFromString($columnKey);
>>>>>>> main
            }

            return self::cellColumn($cell);
        }

        $cellAddress = $cellAddress ?? '';
        if ($cell != null) {
            [,, $sheetName] = Helpers::extractWorksheet($cellAddress, $cell);
            [,, $cellAddress] = Helpers::extractCellAddresses($cellAddress, true, $cell->getWorksheet(), $sheetName);
        }
        [, $cellAddress] = Worksheet::extractSheetTitle($cellAddress, true);
<<<<<<< HEAD
        $cellAddress ??= '';

        if (str_contains($cellAddress, ':')) {
=======
        if (strpos($cellAddress, ':') !== false) {
>>>>>>> main
            [$startAddress, $endAddress] = explode(':', $cellAddress);
            $startAddress = (string) preg_replace('/[^a-z]/i', '', $startAddress);
            $endAddress = (string) preg_replace('/[^a-z]/i', '', $endAddress);

            return range(
<<<<<<< HEAD
                Coordinate::columnIndexFromString($startAddress),
                Coordinate::columnIndexFromString($endAddress)
=======
                (int) Coordinate::columnIndexFromString($startAddress),
                (int) Coordinate::columnIndexFromString($endAddress)
>>>>>>> main
            );
        }

        $cellAddress = (string) preg_replace('/[^a-z]/i', '', $cellAddress);

<<<<<<< HEAD
        try {
            return Coordinate::columnIndexFromString($cellAddress);
        } catch (SpreadsheetException) {
            return ExcelError::NAME();
        }
=======
        return (int) Coordinate::columnIndexFromString($cellAddress);
>>>>>>> main
    }

    /**
     * COLUMNS.
     *
     * Returns the number of columns in an array or reference.
     *
     * Excel Function:
     *        =COLUMNS(cellAddress)
     *
<<<<<<< HEAD
     * @param null|mixed[]|string $cellAddress An array or array formula, or a reference to a range of cells
=======
     * @param null|array|string $cellAddress An array or array formula, or a reference to a range of cells
>>>>>>> main
     *                                          for which you want the number of columns
     *
     * @return int|string The number of columns in cellAddress, or a string if arguments are invalid
     */
    public static function COLUMNS($cellAddress = null)
    {
        if (self::cellAddressNullOrWhitespace($cellAddress)) {
            return 1;
        }
<<<<<<< HEAD
        if (is_string($cellAddress) && ErrorValue::isError($cellAddress, true)) {
            return $cellAddress;
        }
=======
>>>>>>> main
        if (!is_array($cellAddress)) {
            return ExcelError::VALUE();
        }

        reset($cellAddress);
        $isMatrix = (is_numeric(key($cellAddress)));
        [$columns, $rows] = Calculation::getMatrixDimensions($cellAddress);

        if ($isMatrix) {
            return $rows;
        }

        return $columns;
    }

<<<<<<< HEAD
    private static function cellRow(?Cell $cell): int|string
    {
        return ($cell !== null) ? self::convert0ToName($cell->getRow()) : 1;
    }

    private static function convert0ToName(int|string $result): int|string
    {
        if (is_int($result) && ($result <= 0 || $result > 1048576)) {
            return ExcelError::NAME();
        }

        return $result;
=======
    private static function cellRow(?Cell $cell): int
    {
        return ($cell !== null) ? $cell->getRow() : 1;
>>>>>>> main
    }

    /**
     * ROW.
     *
     * Returns the row number of the given cell reference
     *     If the cell reference is a range of cells, ROW returns the row numbers of each row in the reference
     *        as a vertical array.
     *     If cell reference is omitted, and the function is being called through the calculation engine,
     *        then it is assumed to be the reference of the cell in which the ROW function appears;
     *        otherwise this function returns 1.
     *
     * Excel Function:
     *        =ROW([cellAddress])
     *
<<<<<<< HEAD
     * @param null|mixed[][]|string $cellAddress A reference to a range of cells for which you want the row numbers
     *
     * @return int|mixed[]|string
     */
    public static function ROW($cellAddress = null, ?Cell $cell = null): int|string|array
=======
     * @param null|array|string $cellAddress A reference to a range of cells for which you want the row numbers
     *
     * @return int|mixed[]|string
     */
    public static function ROW($cellAddress = null, ?Cell $cell = null)
>>>>>>> main
    {
        if (self::cellAddressNullOrWhitespace($cellAddress)) {
            return self::cellRow($cell);
        }

        if (is_array($cellAddress)) {
            foreach ($cellAddress as $rowKey => $rowValue) {
                foreach ($rowValue as $columnKey => $cellValue) {
                    return (int) preg_replace('/\D/', '', $rowKey);
                }
            }

            return self::cellRow($cell);
        }

        $cellAddress = $cellAddress ?? '';
        if ($cell !== null) {
            [,, $sheetName] = Helpers::extractWorksheet($cellAddress, $cell);
            [,, $cellAddress] = Helpers::extractCellAddresses($cellAddress, true, $cell->getWorksheet(), $sheetName);
        }
        [, $cellAddress] = Worksheet::extractSheetTitle($cellAddress, true);
<<<<<<< HEAD
        $cellAddress ??= '';
        if (str_contains($cellAddress, ':')) {
            [$startAddress, $endAddress] = explode(':', $cellAddress);
            $startAddress = (int) (string) preg_replace('/\D/', '', $startAddress);
            $endAddress = (int) (string) preg_replace('/\D/', '', $endAddress);

            return array_map(
                fn ($value): array => [$value],
=======
        if (strpos($cellAddress, ':') !== false) {
            [$startAddress, $endAddress] = explode(':', $cellAddress);
            $startAddress = (string) preg_replace('/\D/', '', $startAddress);
            $endAddress = (string) preg_replace('/\D/', '', $endAddress);

            return array_map(
                function ($value) {
                    return [$value];
                },
>>>>>>> main
                range($startAddress, $endAddress)
            );
        }
        [$cellAddress] = explode(':', $cellAddress);

<<<<<<< HEAD
        return self::convert0ToName((int) preg_replace('/\D/', '', $cellAddress));
=======
        return (int) preg_replace('/\D/', '', $cellAddress);
>>>>>>> main
    }

    /**
     * ROWS.
     *
     * Returns the number of rows in an array or reference.
     *
     * Excel Function:
     *        =ROWS(cellAddress)
     *
<<<<<<< HEAD
     * @param null|mixed[]|string $cellAddress An array or array formula, or a reference to a range of cells
=======
     * @param null|array|string $cellAddress An array or array formula, or a reference to a range of cells
>>>>>>> main
     *                                          for which you want the number of rows
     *
     * @return int|string The number of rows in cellAddress, or a string if arguments are invalid
     */
    public static function ROWS($cellAddress = null)
    {
        if (self::cellAddressNullOrWhitespace($cellAddress)) {
            return 1;
        }
<<<<<<< HEAD
        if (is_string($cellAddress) && ErrorValue::isError($cellAddress, true)) {
            return $cellAddress;
        }
=======
>>>>>>> main
        if (!is_array($cellAddress)) {
            return ExcelError::VALUE();
        }

        reset($cellAddress);
        $isMatrix = (is_numeric(key($cellAddress)));
        [$columns, $rows] = Calculation::getMatrixDimensions($cellAddress);

        if ($isMatrix) {
            return $columns;
        }

        return $rows;
    }
}
