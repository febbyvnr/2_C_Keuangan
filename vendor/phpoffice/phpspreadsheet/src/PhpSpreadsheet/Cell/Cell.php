<?php

namespace PhpOffice\PhpSpreadsheet\Cell;

use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Calculation\Exception as CalculationException;
use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use PhpOffice\PhpSpreadsheet\Collection\Cells;
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;
=======
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use PhpOffice\PhpSpreadsheet\Collection\Cells;
use PhpOffice\PhpSpreadsheet\Exception;
>>>>>>> main
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date as SharedDate;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\CellStyleAssessor;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Stringable;

class Cell implements Stringable
{
    /**
     * Value binder to use.
     */
    private static ?IValueBinder $valueBinder = null;

    /**
     * Value of the cell.
     */
    private mixed $value;
=======
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Cell
{
    /**
     * Value binder to use.
     *
     * @var IValueBinder
     */
    private static $valueBinder;

    /**
     * Value of the cell.
     *
     * @var mixed
     */
    private $value;
>>>>>>> main

    /**
     *    Calculated value of the cell (used for caching)
     *    This returns the value last calculated by MS Excel or whichever spreadsheet program was used to
     *        create the original spreadsheet file.
     *    Note that this value is not guaranteed to reflect the actual calculated value because it is
     *        possible that auto-calculation was disabled in the original spreadsheet, and underlying data
     *        values used by the formula have changed since it was last calculated.
     *
     * @var mixed
     */
    private $calculatedValue;

    /**
     * Type of the cell data.
<<<<<<< HEAD
     */
    private string $dataType;

    /**
     * The collection of cells that this cell belongs to (i.e. The Cell Collection for the parent Worksheet).
     */
    private ?Cells $parent;

    /**
     * Index to the cellXf reference for the styling of this cell.
     */
    private int $xfIndex = 0;
=======
     *
     * @var string
     */
    private $dataType;

    /**
     * The collection of cells that this cell belongs to (i.e. The Cell Collection for the parent Worksheet).
     *
     * @var ?Cells
     */
    private $parent;

    /**
     * Index to the cellXf reference for the styling of this cell.
     *
     * @var int
     */
    private $xfIndex = 0;
>>>>>>> main

    /**
     * Attributes of the formula.
     *
<<<<<<< HEAD
     * @var null|array<string, string>
     */
    private ?array $formulaAttributes = null;

    private IgnoredErrors $ignoredErrors;
=======
     * @var ?array
     */
    private $formulaAttributes;

    /** @var IgnoredErrors */
    private $ignoredErrors;
>>>>>>> main

    /**
     * Update the cell into the cell collection.
     *
<<<<<<< HEAD
     * @throws SpreadsheetException
=======
     * @return $this
>>>>>>> main
     */
    public function updateInCollection(): self
    {
        $parent = $this->parent;
        if ($parent === null) {
<<<<<<< HEAD
            throw new SpreadsheetException('Cannot update when cell is not bound to a worksheet');
=======
            throw new Exception('Cannot update when cell is not bound to a worksheet');
>>>>>>> main
        }
        $parent->update($this);

        return $this;
    }

    public function detach(): void
    {
        $this->parent = null;
    }

    public function attach(Cells $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Create a new Cell.
     *
<<<<<<< HEAD
     * @throws SpreadsheetException
     */
    public function __construct(mixed $value, ?string $dataType, Worksheet $worksheet)
=======
     * @param mixed $value
     */
    public function __construct($value, ?string $dataType, Worksheet $worksheet)
>>>>>>> main
    {
        // Initialise cell value
        $this->value = $value;

        // Set worksheet cache
        $this->parent = $worksheet->getCellCollection();

        // Set datatype?
        if ($dataType !== null) {
            if ($dataType == DataType::TYPE_STRING2) {
                $dataType = DataType::TYPE_STRING;
            }
            $this->dataType = $dataType;
<<<<<<< HEAD
        } else {
            $valueBinder = $worksheet->getParent()?->getValueBinder() ?? self::getValueBinder();
            if ($valueBinder->bindValue($this, $value) === false) {
                throw new SpreadsheetException('Value could not be bound to cell.');
            }
=======
        } elseif (self::getValueBinder()->bindValue($this, $value) === false) {
            throw new Exception('Value could not be bound to cell.');
>>>>>>> main
        }
        $this->ignoredErrors = new IgnoredErrors();
    }

    /**
     * Get cell coordinate column.
     *
<<<<<<< HEAD
     * @throws SpreadsheetException
     */
    public function getColumn(): string
    {
        $parent = $this->parent;
        if ($parent === null) {
            throw new SpreadsheetException('Cannot get column when cell is not bound to a worksheet');
=======
     * @return string
     */
    public function getColumn()
    {
        $parent = $this->parent;
        if ($parent === null) {
            throw new Exception('Cannot get column when cell is not bound to a worksheet');
>>>>>>> main
        }

        return $parent->getCurrentColumn();
    }

    /**
     * Get cell coordinate row.
     *
<<<<<<< HEAD
     * @throws SpreadsheetException
     */
    public function getRow(): int
    {
        $parent = $this->parent;
        if ($parent === null) {
            throw new SpreadsheetException('Cannot get row when cell is not bound to a worksheet');
=======
     * @return int
     */
    public function getRow()
    {
        $parent = $this->parent;
        if ($parent === null) {
            throw new Exception('Cannot get row when cell is not bound to a worksheet');
>>>>>>> main
        }

        return $parent->getCurrentRow();
    }

    /**
     * Get cell coordinate.
     *
<<<<<<< HEAD
     * @throws SpreadsheetException
     */
    public function getCoordinate(): string
=======
     * @return string
     */
    public function getCoordinate()
>>>>>>> main
    {
        $parent = $this->parent;
        if ($parent !== null) {
            $coordinate = $parent->getCurrentCoordinate();
        } else {
            $coordinate = null;
        }
        if ($coordinate === null) {
<<<<<<< HEAD
            throw new SpreadsheetException('Coordinate no longer exists');
=======
            throw new Exception('Coordinate no longer exists');
>>>>>>> main
        }

        return $coordinate;
    }

    /**
     * Get cell value.
<<<<<<< HEAD
     */
    public function getValue(): mixed
=======
     *
     * @return mixed
     */
    public function getValue()
>>>>>>> main
    {
        return $this->value;
    }

<<<<<<< HEAD
    public function getValueString(): string
    {
        return StringHelper::convertToString($this->value, false);
    }

=======
>>>>>>> main
    /**
     * Get cell value with formatting.
     */
    public function getFormattedValue(): string
    {
<<<<<<< HEAD
        $currentCalendar = SharedDate::getExcelCalendar();
        SharedDate::setExcelCalendar($this->getWorksheet()->getParent()?->getExcelCalendar());
        $formattedValue = (string) NumberFormat::toFormattedString(
            $this->getCalculatedValueString(),
            (string) $this->getStyle()->getNumberFormat()->getFormatCode(true)
        );
        SharedDate::setExcelCalendar($currentCalendar);

        return $formattedValue;
    }

    protected static function updateIfCellIsTableHeader(?Worksheet $workSheet, self $cell, mixed $oldValue, mixed $newValue): void
    {
        $oldValue = StringHelper::convertToString($oldValue, false);
        $newValue = StringHelper::convertToString($newValue, false);
        if (StringHelper::strToLower($oldValue) === StringHelper::strToLower($newValue) || $workSheet === null) {
=======
        return (string) NumberFormat::toFormattedString(
            $this->getCalculatedValue(),
            (string) $this->getStyle()->getNumberFormat()->getFormatCode()
        );
    }

    /**
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    protected static function updateIfCellIsTableHeader(?Worksheet $workSheet, self $cell, $oldValue, $newValue): void
    {
        if (StringHelper::strToLower($oldValue ?? '') === StringHelper::strToLower($newValue ?? '') || $workSheet === null) {
>>>>>>> main
            return;
        }

        foreach ($workSheet->getTableCollection() as $table) {
            /** @var Table $table */
            if ($cell->isInRange($table->getRange())) {
                $rangeRowsColumns = Coordinate::getRangeBoundaries($table->getRange());
                if ($cell->getRow() === (int) $rangeRowsColumns[0][1]) {
                    Table\Column::updateStructuredReferences($workSheet, $oldValue, $newValue);
                }

                return;
            }
        }
    }

    /**
     * Set cell value.
     *
     *    Sets the value for a cell, automatically determining the datatype using the value binder
     *
     * @param mixed $value Value
     * @param null|IValueBinder $binder Value Binder to override the currently set Value Binder
     *
<<<<<<< HEAD
     * @throws SpreadsheetException
     */
    public function setValue(mixed $value, ?IValueBinder $binder = null): self
    {
        if ($this->hadHyperlink) {
            $this->clearHyperlink();
        }
        // Cells?->Worksheet?->Spreadsheet
        $binder ??= $this->parent?->getParent()?->getParent()?->getValueBinder() ?? self::getValueBinder();
        if (!$binder->bindValue($this, $value)) {
            throw new SpreadsheetException('Value could not be bound to cell.');
=======
     * @throws Exception
     *
     * @return $this
     */
    public function setValue($value, ?IValueBinder $binder = null): self
    {
        $binder ??= self::getValueBinder();
        if (!$binder->bindValue($this, $value)) {
            throw new Exception('Value could not be bound to cell.');
>>>>>>> main
        }

        return $this;
    }

<<<<<<< HEAD
    private bool $hadHyperlink = false;

    /** @internal */
    public function setHadHyperlink(bool $hadHyperlink): void
    {
        $this->hadHyperlink = $hadHyperlink;
    }

    private function clearHyperlink(): void
    {
        $worksheet = $this->getWorksheetOrNull();
        if ($worksheet !== null) {
            $coordinate = $this->getCoordinate();
            $worksheet->setHyperlink($coordinate, null);
        }
        $this->hadHyperlink = false;
    }

=======
>>>>>>> main
    /**
     * Set the value for a cell, with the explicit data type passed to the method (bypassing any use of the value binder).
     *
     * @param mixed $value Value
     * @param string $dataType Explicit data type, see DataType::TYPE_*
<<<<<<< HEAD
     *        This parameter is currently optional (default = string).
     *        Omitting it is ***DEPRECATED***, and the default will be removed in a future release.
=======
>>>>>>> main
     *        Note that PhpSpreadsheet does not validate that the value and datatype are consistent, in using this
     *             method, then it is your responsibility as an end-user developer to validate that the value and
     *             the datatype match.
     *       If you do mismatch value and datatype, then the value you enter may be changed to match the datatype
     *          that you specify.
     *
<<<<<<< HEAD
     * @throws SpreadsheetException
     */
    public function setValueExplicit(mixed $value, string $dataType = DataType::TYPE_STRING): self
    {
        if ($this->hadHyperlink) {
            $this->clearHyperlink();
        }
        $oldValue = $this->value;
        $quotePrefix = false;
=======
     * @return Cell
     */
    public function setValueExplicit($value, string $dataType = DataType::TYPE_STRING)
    {
        $oldValue = $this->value;
>>>>>>> main

        // set the value according to data type
        switch ($dataType) {
            case DataType::TYPE_NULL:
                $this->value = null;

                break;
            case DataType::TYPE_STRING2:
                $dataType = DataType::TYPE_STRING;
                // no break
            case DataType::TYPE_STRING:
                // Synonym for string
<<<<<<< HEAD
                if (is_string($value) && strlen($value) > 1 && $value[0] === '=') {
                    $quotePrefix = true;
                }
                // no break
            case DataType::TYPE_INLINE:
                // Rich text
                $value2 = StringHelper::convertToString($value, true);
                // Cells?->Worksheet?->Spreadsheet
                $binder = $this->parent?->getParent()?->getParent()?->getValueBinder();
                $preserveCr = false;
                if ($binder !== null && method_exists($binder, 'getPreserveCr')) {
                    /** @var bool */
                    $preserveCr = $binder->getPreserveCr();
                }
                $this->value = DataType::checkString(($value instanceof RichText) ? $value : $value2, $preserveCr);

                break;
            case DataType::TYPE_NUMERIC:
                if ($value !== null && !is_bool($value) && !is_numeric($value)) {
                    throw new SpreadsheetException('Invalid numeric value for datatype Numeric');
=======
            case DataType::TYPE_INLINE:
                // Rich text
                $this->value = DataType::checkString($value);

                break;
            case DataType::TYPE_NUMERIC:
                if (is_string($value) && !is_numeric($value)) {
                    throw new Exception('Invalid numeric value for datatype Numeric');
>>>>>>> main
                }
                $this->value = 0 + $value;

                break;
            case DataType::TYPE_FORMULA:
<<<<<<< HEAD
                $this->value = StringHelper::convertToString($value, true);
=======
                $this->value = (string) $value;
>>>>>>> main

                break;
            case DataType::TYPE_BOOL:
                $this->value = (bool) $value;

                break;
            case DataType::TYPE_ISO_DATE:
                $this->value = SharedDate::convertIsoDate($value);
                $dataType = DataType::TYPE_NUMERIC;

                break;
<<<<<<< HEAD
            case DataType::TYPE_DRAWING_IN_CELL:
                if ($value instanceof BaseDrawing) {
                    $this->value = $value;
                } else {
                    throw new SpreadsheetException('Item is not a drawing');
                }

                break;
=======
>>>>>>> main
            case DataType::TYPE_ERROR:
                $this->value = DataType::checkErrorCode($value);

                break;
            default:
<<<<<<< HEAD
                throw new SpreadsheetException('Invalid datatype: ' . $dataType);
=======
                throw new Exception('Invalid datatype: ' . $dataType);
>>>>>>> main
        }

        // set the datatype
        $this->dataType = $dataType;

        $this->updateInCollection();
        $cellCoordinate = $this->getCoordinate();
<<<<<<< HEAD
        self::updateIfCellIsTableHeader($this->getParent()?->getParent(), $this, $oldValue, $value);
        $worksheet = $this->getWorksheet();
        $spreadsheet = $worksheet->getParent();
        if (isset($spreadsheet) && $spreadsheet->getIndex($worksheet, true) >= 0) {
            $originalSelected = $worksheet->getSelectedCells();
            $activeSheetIndex = $spreadsheet->getActiveSheetIndex();
            $style = $this->getStyle();
            $oldQuotePrefix = $style->getQuotePrefix();
            if ($oldQuotePrefix !== $quotePrefix) {
                $style->setQuotePrefix($quotePrefix);
            }
            $worksheet->setSelectedCells($originalSelected);
            if ($activeSheetIndex >= 0) {
                $spreadsheet->setActiveSheetIndex($activeSheetIndex);
            }
        }

        return $this->getParent()?->get($cellCoordinate) ?? $this;
=======
        self::updateIfCellIsTableHeader($this->getParent()->getParent(), $this, $oldValue, $value); // @phpstan-ignore-line

        return $this->getParent()->get($cellCoordinate); // @phpstan-ignore-line
>>>>>>> main
    }

    public const CALCULATE_DATE_TIME_ASIS = 0;
    public const CALCULATE_DATE_TIME_FLOAT = 1;
    public const CALCULATE_TIME_FLOAT = 2;

<<<<<<< HEAD
    private static int $calculateDateTimeType = self::CALCULATE_DATE_TIME_ASIS;
=======
    /** @var int */
    private static $calculateDateTimeType = self::CALCULATE_DATE_TIME_ASIS;
>>>>>>> main

    public static function getCalculateDateTimeType(): int
    {
        return self::$calculateDateTimeType;
    }

<<<<<<< HEAD
    /** @throws CalculationException */
    public static function setCalculateDateTimeType(int $calculateDateTimeType): void
    {
        self::$calculateDateTimeType = match ($calculateDateTimeType) {
            self::CALCULATE_DATE_TIME_ASIS, self::CALCULATE_DATE_TIME_FLOAT, self::CALCULATE_TIME_FLOAT => $calculateDateTimeType,
            default => throw new CalculationException("Invalid value $calculateDateTimeType for calculated date time type"),
        };
=======
    public static function setCalculateDateTimeType(int $calculateDateTimeType): void
    {
        switch ($calculateDateTimeType) {
            case self::CALCULATE_DATE_TIME_ASIS:
            case self::CALCULATE_DATE_TIME_FLOAT:
            case self::CALCULATE_TIME_FLOAT:
                self::$calculateDateTimeType = $calculateDateTimeType;

                break;
            default:
                throw new \PhpOffice\PhpSpreadsheet\Calculation\Exception("Invalid value $calculateDateTimeType for calculated date time type");
        }
>>>>>>> main
    }

    /**
     * Convert date, time, or datetime from int to float if desired.
<<<<<<< HEAD
     */
    private function convertDateTimeInt(mixed $result): mixed
=======
     *
     * @param mixed $result
     *
     * @return mixed
     */
    private function convertDateTimeInt($result)
>>>>>>> main
    {
        if (is_int($result)) {
            if (self::$calculateDateTimeType === self::CALCULATE_TIME_FLOAT) {
                if (SharedDate::isDateTime($this, $result, false)) {
                    $result = (float) $result;
                }
            } elseif (self::$calculateDateTimeType === self::CALCULATE_DATE_TIME_FLOAT) {
                if (SharedDate::isDateTime($this, $result, true)) {
                    $result = (float) $result;
                }
            }
        }

        return $result;
    }

    /**
<<<<<<< HEAD
     * Get calculated cell value converted to string.
     */
    public function getCalculatedValueString(): string
    {
        $value = $this->getCalculatedValue();
        while (is_array($value)) {
            $value = array_shift($value);
        }

        return StringHelper::convertToString($value, false);
    }

    /**
=======
>>>>>>> main
     * Get calculated cell value.
     *
     * @param bool $resetLog Whether the calculation engine logger should be reset or not
     *
<<<<<<< HEAD
     * @throws CalculationException
     */
    public function getCalculatedValue(bool $resetLog = true): mixed
    {
        $title = 'unknown';
        $oldAttributes = $this->formulaAttributes;
        $oldAttributesT = $oldAttributes['t'] ?? '';
        $coordinate = $this->getCoordinate();
        $oldAttributesRef = $oldAttributes['ref'] ?? $coordinate;
        $originalValue = $this->value;
        $originalDataType = $this->dataType;
        $this->formulaAttributes = [];
        $spill = false;

        if ($this->dataType === DataType::TYPE_FORMULA) {
            try {
                $currentCalendar = SharedDate::getExcelCalendar();
                SharedDate::setExcelCalendar($this->getWorksheet()->getParent()?->getExcelCalendar());
                $thisworksheet = $this->getWorksheet();
                $index = $thisworksheet->getParentOrThrow()->getActiveSheetIndex();
                $selected = $thisworksheet->getSelectedCells();
                $title = $thisworksheet->getTitle();
                $calculation = Calculation::getInstance($thisworksheet->getParent());
                $result = $calculation->calculateCellValue($this, $resetLog);
                $result = $this->convertDateTimeInt($result);
                $thisworksheet->setSelectedCells($selected);
                $thisworksheet->getParentOrThrow()->setActiveSheetIndex($index);
                if (is_array($result) && $calculation->getInstanceArrayReturnType() !== Calculation::RETURN_ARRAY_AS_ARRAY) {
=======
     * @return mixed
     */
    public function getCalculatedValue(bool $resetLog = true)
    {
        if ($this->dataType === DataType::TYPE_FORMULA) {
            try {
                $index = $this->getWorksheet()->getParentOrThrow()->getActiveSheetIndex();
                $selected = $this->getWorksheet()->getSelectedCells();
                $result = Calculation::getInstance(
                    $this->getWorksheet()->getParent()
                )->calculateCellValue($this, $resetLog);
                $result = $this->convertDateTimeInt($result);
                $this->getWorksheet()->setSelectedCells($selected);
                $this->getWorksheet()->getParentOrThrow()->setActiveSheetIndex($index);
                //    We don't yet handle array returns
                if (is_array($result)) {
>>>>>>> main
                    while (is_array($result)) {
                        $result = array_shift($result);
                    }
                }
<<<<<<< HEAD
                if (
                    !is_array($result)
                    && $calculation->getInstanceArrayReturnType() === Calculation::RETURN_ARRAY_AS_ARRAY
                    && $oldAttributesT === 'array'
                    && ($oldAttributesRef === $coordinate || $oldAttributesRef === "$coordinate:$coordinate")
                ) {
                    $result = [$result];
                }
                // if return_as_array for formula like '=sheet!cell'
                if (is_array($result) && count($result) === 1) {
                    $resultKey = array_keys($result)[0];
                    $resultValue = $result[$resultKey];
                    if (is_int($resultKey) && is_array($resultValue) && count($resultValue) === 1) {
                        $resultKey2 = array_keys($resultValue)[0];
                        $resultValue2 = $resultValue[$resultKey2];
                        if (is_string($resultKey2) && !is_array($resultValue2) && preg_match('/[a-zA-Z]{1,3}/', $resultKey2) === 1) {
                            $result = $resultValue2;
                        }
                    }
                }
                $newColumn = $this->getColumn();
                if (is_array($result)) {
                    $result = self::convertSpecialArray($result);
                    $this->formulaAttributes['t'] = 'array';
                    $this->formulaAttributes['ref'] = $maxCoordinate = $coordinate;
                    $newRow = $row = $this->getRow();
                    $column = $this->getColumn();
                    foreach ($result as $resultRow) {
                        if (is_array($resultRow)) {
                            $newColumn = $column;
                            foreach ($resultRow as $resultValue) {
                                if ($row !== $newRow || $column !== $newColumn) {
                                    $maxCoordinate = $newColumn . $newRow;
                                    if ($thisworksheet->getCell($newColumn . $newRow)->getValue() !== null) {
                                        if (!Coordinate::coordinateIsInsideRange($oldAttributesRef, $newColumn . $newRow)) {
                                            $spill = true;

                                            break;
                                        }
                                    }
                                }
                                /** @var string $newColumn */
                                StringHelper::stringIncrement($newColumn);
                            }
                            ++$newRow;
                        } else {
                            if ($row !== $newRow || $column !== $newColumn) {
                                $maxCoordinate = $newColumn . $newRow;
                                if ($thisworksheet->getCell($newColumn . $newRow)->getValue() !== null) {
                                    if (!Coordinate::coordinateIsInsideRange($oldAttributesRef, $newColumn . $newRow)) {
                                        $spill = true;
                                    }
                                }
                            }
                            StringHelper::stringIncrement($newColumn);
                        }
                        if ($spill) {
                            break;
                        }
                    }
                    if (!$spill) {
                        $this->formulaAttributes['ref'] .= ":$maxCoordinate";
                    }
                    $thisworksheet->getCell($column . $row);
                }
                if (is_array($result)) {
                    if ($oldAttributes !== null && $calculation->getInstanceArrayReturnType() === Calculation::RETURN_ARRAY_AS_ARRAY) {
                        if (($oldAttributesT) === 'array') {
                            $thisworksheet = $this->getWorksheet();
                            $coordinate = $this->getCoordinate();
                            $ref = $oldAttributesRef;
                            if (preg_match('/^([A-Z]{1,3})([0-9]{1,7})(:([A-Z]{1,3})([0-9]{1,7}))?$/', $ref, $matches) === 1) {
                                if (isset($matches[3])) {
                                    $minCol = $matches[1];
                                    $minRow = (int) $matches[2];
                                    $maxCol = $matches[4];
                                    StringHelper::stringIncrement($maxCol);
                                    $maxRow = (int) $matches[5];
                                    for ($row = $minRow; $row <= $maxRow; ++$row) {
                                        for ($col = $minCol; $col !== $maxCol; StringHelper::stringIncrement($col)) {
                                            /** @var string $col */
                                            if ("$col$row" !== $coordinate) {
                                                $thisworksheet->getCell("$col$row")->setValue(null);
                                            }
                                        }
                                    }
                                }
                            }
                            $thisworksheet->getCell($coordinate);
                        }
                    }
                }
                if ($spill) {
                    $result = ExcelError::SPILL();
                }
                if (is_array($result)) {
                    $newRow = $row = $this->getRow();
                    $newColumn = $column = $this->getColumn();
                    foreach ($result as $resultRow) {
                        if (is_array($resultRow)) {
                            $newColumn = $column;
                            foreach ($resultRow as $resultValue) {
                                if ($row !== $newRow || $column !== $newColumn) {
                                    $thisworksheet
                                        ->getCell($newColumn . $newRow)
                                        ->setValue($resultValue);
                                }
                                StringHelper::stringIncrement($newColumn);
                            }
                            ++$newRow;
                        } else {
                            if ($row !== $newRow || $column !== $newColumn) {
                                $thisworksheet->getCell($newColumn . $newRow)->setValue($resultRow);
                            }
                            StringHelper::stringIncrement($newColumn);
                        }
                    }
                    $thisworksheet->getCell($column . $row);
                    $this->value = $originalValue;
                    $this->dataType = $originalDataType;
                }
            } catch (SpreadsheetException $ex) {
                SharedDate::setExcelCalendar($currentCalendar);
=======
            } catch (Exception $ex) {
>>>>>>> main
                if (($ex->getMessage() === 'Unable to access External Workbook') && ($this->calculatedValue !== null)) {
                    return $this->calculatedValue; // Fallback for calculations referencing external files.
                } elseif (preg_match('/[Uu]ndefined (name|offset: 2|array key 2)/', $ex->getMessage()) === 1) {
                    return ExcelError::NAME();
                }

<<<<<<< HEAD
                throw new CalculationException(
                    $title . '!' . $this->getCoordinate() . ' -> ' . $ex->getMessage(),
=======
                throw new \PhpOffice\PhpSpreadsheet\Calculation\Exception(
                    $this->getWorksheet()->getTitle() . '!' . $this->getCoordinate() . ' -> ' . $ex->getMessage(),
>>>>>>> main
                    $ex->getCode(),
                    $ex
                );
            }
<<<<<<< HEAD
            SharedDate::setExcelCalendar($currentCalendar);

            if ($result === Functions::NOT_YET_IMPLEMENTED) {
                $this->formulaAttributes = $oldAttributes;

=======

            if ($result === '#Not Yet Implemented') {
>>>>>>> main
                return $this->calculatedValue; // Fallback if calculation engine does not support the formula.
            }

            return $result;
        } elseif ($this->value instanceof RichText) {
            return $this->value->getPlainText();
        }

        return $this->convertDateTimeInt($this->value);
    }

    /**
<<<<<<< HEAD
     * Convert array like the following (preserve values, lose indexes):
     * [
     *   rowNumber1 => [colLetter1 => value, colLetter2 => value ...],
     *   rowNumber2 => [colLetter1 => value, colLetter2 => value ...],
     *   ...
     * ].
     *
     * @param mixed[] $array
     *
     * @return mixed[]
     */
    private static function convertSpecialArray(array $array): array
    {
        $newArray = [];
        foreach ($array as $rowIndex => $row) {
            if (!is_int($rowIndex) || $rowIndex <= 0 || !is_array($row)) {
                return $array;
            }
            $keys = array_keys($row);
            $key0 = $keys[0] ?? '';
            if (!is_string($key0)) {
                return $array;
            }
            $newArray[] = array_values($row);
        }

        return $newArray;
    }

    /**
=======
>>>>>>> main
     * Set old calculated value (cached).
     *
     * @param mixed $originalValue Value
     */
<<<<<<< HEAD
    public function setCalculatedValue(mixed $originalValue, bool $tryNumeric = true): self
    {
        if ($originalValue !== null) {
            $this->calculatedValue = ($tryNumeric && is_numeric($originalValue)) ? (0 + $originalValue) : $originalValue;
=======
    public function setCalculatedValue($originalValue): self
    {
        if ($originalValue !== null) {
            $this->calculatedValue = (is_numeric($originalValue)) ? (float) $originalValue : $originalValue;
>>>>>>> main
        }

        return $this->updateInCollection();
    }

    /**
     *    Get old calculated value (cached)
     *    This returns the value last calculated by MS Excel or whichever spreadsheet program was used to
     *        create the original spreadsheet file.
     *    Note that this value is not guaranteed to reflect the actual calculated value because it is
     *        possible that auto-calculation was disabled in the original spreadsheet, and underlying data
     *        values used by the formula have changed since it was last calculated.
<<<<<<< HEAD
     */
    public function getOldCalculatedValue(): mixed
=======
     *
     * @return mixed
     */
    public function getOldCalculatedValue()
>>>>>>> main
    {
        return $this->calculatedValue;
    }

    /**
     * Get cell data type.
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * Set cell data type.
     *
     * @param string $dataType see DataType::TYPE_*
     */
<<<<<<< HEAD
    public function setDataType(string $dataType): self
    {
        $this->setValueExplicit($this->value, $dataType);

        return $this;
=======
    public function setDataType($dataType): self
    {
        if ($dataType == DataType::TYPE_STRING2) {
            $dataType = DataType::TYPE_STRING;
        }
        $this->dataType = $dataType;

        return $this->updateInCollection();
>>>>>>> main
    }

    /**
     * Identify if the cell contains a formula.
     */
    public function isFormula(): bool
    {
        return $this->dataType === DataType::TYPE_FORMULA && $this->getStyle()->getQuotePrefix() === false;
    }

    /**
     *    Does this cell contain Data validation rules?
<<<<<<< HEAD
     *
     * @throws SpreadsheetException
=======
>>>>>>> main
     */
    public function hasDataValidation(): bool
    {
        if (!isset($this->parent)) {
<<<<<<< HEAD
            throw new SpreadsheetException('Cannot check for data validation when cell is not bound to a worksheet');
=======
            throw new Exception('Cannot check for data validation when cell is not bound to a worksheet');
>>>>>>> main
        }

        return $this->getWorksheet()->dataValidationExists($this->getCoordinate());
    }

    /**
     * Get Data validation rules.
<<<<<<< HEAD
     *
     * @throws SpreadsheetException
=======
>>>>>>> main
     */
    public function getDataValidation(): DataValidation
    {
        if (!isset($this->parent)) {
<<<<<<< HEAD
            throw new SpreadsheetException('Cannot get data validation for cell that is not bound to a worksheet');
=======
            throw new Exception('Cannot get data validation for cell that is not bound to a worksheet');
>>>>>>> main
        }

        return $this->getWorksheet()->getDataValidation($this->getCoordinate());
    }

    /**
     * Set Data validation rules.
<<<<<<< HEAD
     *
     * @throws SpreadsheetException
=======
>>>>>>> main
     */
    public function setDataValidation(?DataValidation $dataValidation = null): self
    {
        if (!isset($this->parent)) {
<<<<<<< HEAD
            throw new SpreadsheetException('Cannot set data validation for cell that is not bound to a worksheet');
=======
            throw new Exception('Cannot set data validation for cell that is not bound to a worksheet');
>>>>>>> main
        }

        $this->getWorksheet()->setDataValidation($this->getCoordinate(), $dataValidation);

        return $this->updateInCollection();
    }

    /**
     * Does this cell contain valid value?
     */
    public function hasValidValue(): bool
    {
        $validator = new DataValidator();

        return $validator->isValid($this);
    }

    /**
     * Does this cell contain a Hyperlink?
<<<<<<< HEAD
     *
     * @throws SpreadsheetException
=======
>>>>>>> main
     */
    public function hasHyperlink(): bool
    {
        if (!isset($this->parent)) {
<<<<<<< HEAD
            throw new SpreadsheetException('Cannot check for hyperlink when cell is not bound to a worksheet');
=======
            throw new Exception('Cannot check for hyperlink when cell is not bound to a worksheet');
>>>>>>> main
        }

        return $this->getWorksheet()->hyperlinkExists($this->getCoordinate());
    }

    /**
     * Get Hyperlink.
<<<<<<< HEAD
     *
     * @throws SpreadsheetException
=======
>>>>>>> main
     */
    public function getHyperlink(): Hyperlink
    {
        if (!isset($this->parent)) {
<<<<<<< HEAD
            throw new SpreadsheetException('Cannot get hyperlink for cell that is not bound to a worksheet');
        }

        return $this->getWorksheet()
            ->getHyperlink($this->getCoordinate());
=======
            throw new Exception('Cannot get hyperlink for cell that is not bound to a worksheet');
        }

        return $this->getWorksheet()->getHyperlink($this->getCoordinate());
>>>>>>> main
    }

    /**
     * Set Hyperlink.
<<<<<<< HEAD
     *
     * @throws SpreadsheetException
=======
>>>>>>> main
     */
    public function setHyperlink(?Hyperlink $hyperlink = null): self
    {
        if (!isset($this->parent)) {
<<<<<<< HEAD
            throw new SpreadsheetException('Cannot set hyperlink for cell that is not bound to a worksheet');
        }

        $this->getWorksheet()
            ->setHyperlink($this->getCoordinate(), $hyperlink);
=======
            throw new Exception('Cannot set hyperlink for cell that is not bound to a worksheet');
        }

        $this->getWorksheet()->setHyperlink($this->getCoordinate(), $hyperlink);
>>>>>>> main

        return $this->updateInCollection();
    }

    /**
     * Get cell collection.
<<<<<<< HEAD
     */
    public function getParent(): ?Cells
=======
     *
     * @return ?Cells
     */
    public function getParent()
>>>>>>> main
    {
        return $this->parent;
    }

    /**
     * Get parent worksheet.
<<<<<<< HEAD
     *
     * @throws SpreadsheetException
=======
>>>>>>> main
     */
    public function getWorksheet(): Worksheet
    {
        $parent = $this->parent;
        if ($parent !== null) {
            $worksheet = $parent->getParent();
        } else {
            $worksheet = null;
        }

        if ($worksheet === null) {
<<<<<<< HEAD
            throw new SpreadsheetException('Worksheet no longer exists');
=======
            throw new Exception('Worksheet no longer exists');
>>>>>>> main
        }

        return $worksheet;
    }

    public function getWorksheetOrNull(): ?Worksheet
    {
        $parent = $this->parent;
        if ($parent !== null) {
            $worksheet = $parent->getParent();
        } else {
            $worksheet = null;
        }

        return $worksheet;
    }

    /**
     * Is this cell in a merge range.
     */
    public function isInMergeRange(): bool
    {
        return (bool) $this->getMergeRange();
    }

    /**
     * Is this cell the master (top left cell) in a merge range (that holds the actual data value).
     */
    public function isMergeRangeValueCell(): bool
    {
        if ($mergeRange = $this->getMergeRange()) {
            $mergeRange = Coordinate::splitRange($mergeRange);
            [$startCell] = $mergeRange[0];

            return $this->getCoordinate() === $startCell;
        }

        return false;
    }

    /**
     * If this cell is in a merge range, then return the range.
     *
     * @return false|string
     */
    public function getMergeRange()
    {
        foreach ($this->getWorksheet()->getMergeCells() as $mergeRange) {
            if ($this->isInRange($mergeRange)) {
                return $mergeRange;
            }
        }

        return false;
    }

    /**
     * Get cell style.
     */
    public function getStyle(): Style
    {
        return $this->getWorksheet()->getStyle($this->getCoordinate());
    }

    /**
     * Get cell style.
     */
    public function getAppliedStyle(): Style
    {
        if ($this->getWorksheet()->conditionalStylesExists($this->getCoordinate()) === false) {
            return $this->getStyle();
        }
        $range = $this->getWorksheet()->getConditionalRange($this->getCoordinate());
        if ($range === null) {
            return $this->getStyle();
        }

        $matcher = new CellStyleAssessor($this, $range);

        return $matcher->matchConditions($this->getWorksheet()->getConditionalStyles($this->getCoordinate()));
    }

    /**
     * Re-bind parent.
     */
    public function rebindParent(Worksheet $parent): self
    {
        $this->parent = $parent->getCellCollection();

        return $this->updateInCollection();
    }

    /**
     *    Is cell in a specific range?
     *
     * @param string $range Cell range (e.g. A1:A1)
     */
    public function isInRange(string $range): bool
    {
        [$rangeStart, $rangeEnd] = Coordinate::rangeBoundaries($range);

        // Translate properties
        $myColumn = Coordinate::columnIndexFromString($this->getColumn());
        $myRow = $this->getRow();

        // Verify if cell is in range
<<<<<<< HEAD
        return ($rangeStart[0] <= $myColumn) && ($rangeEnd[0] >= $myColumn)
            && ($rangeStart[1] <= $myRow) && ($rangeEnd[1] >= $myRow);
=======
        return ($rangeStart[0] <= $myColumn) && ($rangeEnd[0] >= $myColumn) &&
                ($rangeStart[1] <= $myRow) && ($rangeEnd[1] >= $myRow);
>>>>>>> main
    }

    /**
     * Compare 2 cells.
     *
     * @param Cell $a Cell a
     * @param Cell $b Cell b
     *
     * @return int Result of comparison (always -1 or 1, never zero!)
     */
    public static function compareCells(self $a, self $b): int
    {
        if ($a->getRow() < $b->getRow()) {
            return -1;
        } elseif ($a->getRow() > $b->getRow()) {
            return 1;
        } elseif (Coordinate::columnIndexFromString($a->getColumn()) < Coordinate::columnIndexFromString($b->getColumn())) {
            return -1;
        }

        return 1;
    }

    /**
     * Get value binder to use.
     */
    public static function getValueBinder(): IValueBinder
    {
        if (self::$valueBinder === null) {
            self::$valueBinder = new DefaultValueBinder();
        }

        return self::$valueBinder;
    }

    /**
     * Set value binder to use.
     */
    public static function setValueBinder(IValueBinder $binder): void
    {
        self::$valueBinder = $binder;
    }

    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     */
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $propertyName => $propertyValue) {
            if ((is_object($propertyValue)) && ($propertyName !== 'parent')) {
                $this->$propertyName = clone $propertyValue;
            } else {
                $this->$propertyName = $propertyValue;
            }
        }
    }

    /**
     * Get index to cellXf.
     */
    public function getXfIndex(): int
    {
        return $this->xfIndex;
    }

    /**
     * Set index to cellXf.
     */
    public function setXfIndex(int $indexValue): self
    {
        $this->xfIndex = $indexValue;

        return $this->updateInCollection();
    }

<<<<<<< HEAD
    /**
     * Set the formula attributes.
     *
     * @param null|array<string, string> $attributes
     */
=======
>>>>>>> main
    public function setFormulaAttributes(?array $attributes): self
    {
        $this->formulaAttributes = $attributes;

        return $this;
    }

<<<<<<< HEAD
    /**
     * Get the formula attributes.
     *
     * @return null|array<string, string>
     */
    public function getFormulaAttributes(): mixed
=======
    public function getFormulaAttributes(): ?array
>>>>>>> main
    {
        return $this->formulaAttributes;
    }

    /**
     * Convert to string.
<<<<<<< HEAD
     */
    public function __toString(): string
    {
        $retVal = $this->value;

        return StringHelper::convertToString($retVal, false);
=======
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
>>>>>>> main
    }

    public function getIgnoredErrors(): IgnoredErrors
    {
        return $this->ignoredErrors;
    }
<<<<<<< HEAD

    public function isLocked(): bool
    {
        $protected = $this->parent?->getParent()?->getProtection()?->getSheet();
        if ($protected !== true) {
            return false;
        }
        $locked = $this->getStyle()->getProtection()->getLocked();

        return $locked !== Protection::PROTECTION_UNPROTECTED;
    }

    public function isHiddenOnFormulaBar(): bool
    {
        if ($this->getDataType() !== DataType::TYPE_FORMULA) {
            return false;
        }
        $protected = $this->parent?->getParent()?->getProtection()?->getSheet();
        if ($protected !== true) {
            return false;
        }
        $hidden = $this->getStyle()->getProtection()->getHidden();

        return $hidden !== Protection::PROTECTION_UNPROTECTED;
    }
=======
>>>>>>> main
}
