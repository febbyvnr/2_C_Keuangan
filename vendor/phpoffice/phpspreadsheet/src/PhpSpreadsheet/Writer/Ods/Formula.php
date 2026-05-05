<?php

namespace PhpOffice\PhpSpreadsheet\Writer\Ods;

<<<<<<< HEAD
use Composer\Pcre\Preg;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\DefinedName;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;

class Formula
{
    /** @var string[] */
    private array $definedNames = [];
=======
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\DefinedName;

class Formula
{
    /** @var array */
    private $definedNames = [];
>>>>>>> main

    /**
     * @param DefinedName[] $definedNames
     */
    public function __construct(array $definedNames)
    {
        foreach ($definedNames as $definedName) {
            $this->definedNames[] = $definedName->getName();
        }
    }

    public function convertFormula(string $formula, string $worksheetName = ''): string
    {
        $formula = $this->convertCellReferences($formula, $worksheetName);
        $formula = $this->convertDefinedNames($formula);
<<<<<<< HEAD
        $formula = $this->convertFunctionNames($formula);

        if (!str_starts_with($formula, '=')) {
=======

        if (substr($formula, 0, 1) !== '=') {
>>>>>>> main
            $formula = '=' . $formula;
        }

        return 'of:' . $formula;
    }

    private function convertDefinedNames(string $formula): string
    {
<<<<<<< HEAD
        $splitCount = Preg::matchAllWithOffsets(
            '/' . Calculation::CALCULATION_REGEXP_DEFINEDNAME . '/mui',
            $formula,
            $splitRanges
        );

        $lengths = array_map([StringHelper::class, 'strlenAllowNull'], array_column($splitRanges[0], 0));
=======
        $splitCount = preg_match_all(
            '/' . Calculation::CALCULATION_REGEXP_DEFINEDNAME . '/mui',
            $formula,
            $splitRanges,
            PREG_OFFSET_CAPTURE
        );

        $lengths = array_map('strlen', array_column($splitRanges[0], 0));
>>>>>>> main
        $offsets = array_column($splitRanges[0], 1);
        $values = array_column($splitRanges[0], 0);

        while ($splitCount > 0) {
            --$splitCount;
            $length = $lengths[$splitCount];
            $offset = $offsets[$splitCount];
            $value = $values[$splitCount];

            if (in_array($value, $this->definedNames, true)) {
                $formula = substr($formula, 0, $offset) . '$$' . $value . substr($formula, $offset + $length);
            }
        }

        return $formula;
    }

    private function convertCellReferences(string $formula, string $worksheetName): string
    {
<<<<<<< HEAD
        $splitCount = Preg::matchAllWithOffsets(
            '/' . Calculation::CALCULATION_REGEXP_CELLREF_RELATIVE . '/mui',
            $formula,
            $splitRanges
        );

        $lengths = array_map([StringHelper::class, 'strlenAllowNull'], array_column($splitRanges[0], 0));
=======
        $splitCount = preg_match_all(
            '/' . Calculation::CALCULATION_REGEXP_CELLREF_RELATIVE . '/mui',
            $formula,
            $splitRanges,
            PREG_OFFSET_CAPTURE
        );

        $lengths = array_map('strlen', array_column($splitRanges[0], 0));
>>>>>>> main
        $offsets = array_column($splitRanges[0], 1);

        $worksheets = $splitRanges[2];
        $columns = $splitRanges[6];
        $rows = $splitRanges[7];

<<<<<<< HEAD
        // Replace any commas in the formula with semicolons for Ods
        // If by chance there are commas in worksheet names, then they will be "fixed" again in the loop
        //    because we've already extracted worksheet names with our Preg::matchAllWithOffsets()
=======
        // Replace any commas in the formula with semi-colons for Ods
        // If by chance there are commas in worksheet names, then they will be "fixed" again in the loop
        //    because we've already extracted worksheet names with our preg_match_all()
>>>>>>> main
        $formula = str_replace(',', ';', $formula);
        while ($splitCount > 0) {
            --$splitCount;
            $length = $lengths[$splitCount];
            $offset = $offsets[$splitCount];
            $worksheet = $worksheets[$splitCount][0];
            $column = $columns[$splitCount][0];
            $row = $rows[$splitCount][0];

            $newRange = '';
            if (empty($worksheet)) {
                if (($offset === 0) || ($formula[$offset - 1] !== ':')) {
                    // We need a worksheet
                    $worksheet = $worksheetName;
                }
            } else {
                $worksheet = str_replace("''", "'", trim($worksheet, "'"));
            }
            if (!empty($worksheet)) {
                $newRange = "['" . str_replace("'", "''", $worksheet) . "'";
            } elseif (substr($formula, $offset - 1, 1) !== ':') {
                $newRange = '[';
            }
            $newRange .= '.';

<<<<<<< HEAD
            //if (!empty($column)) { // phpstan says always true
            $newRange .= $column;
            //}
=======
            if (!empty($column)) {
                $newRange .= $column;
            }
>>>>>>> main
            if (!empty($row)) {
                $newRange .= $row;
            }
            // close the wrapping [] unless this is the first part of a range
            $newRange .= substr($formula, $offset + $length, 1) !== ':' ? ']' : '';

            $formula = substr($formula, 0, $offset) . $newRange . substr($formula, $offset + $length);
        }

        return $formula;
    }
<<<<<<< HEAD

    private function convertFunctionNames(string $formula): string
    {
        return Preg::replace(
            [
                '/\b((CEILING|FLOOR)'
                    . '([.](MATH|PRECISE))?)\s*[(]/ui',
                '/\b(CEILING|FLOOR)[.]XCL\s*[(]/ui',
                '/\b(CEILING|FLOOR)[.]ODS\s*[(]/ui',
            ],
            [
                'COM.MICROSOFT.$1(',
                'COM.MICROSOFT.$1(',
                '$1(',
            ],
            $formula
        );
    }
=======
>>>>>>> main
}
