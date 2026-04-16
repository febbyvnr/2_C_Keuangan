<?php

namespace PhpOffice\PhpSpreadsheet\Helper;

<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;

class TextGrid
{
    protected bool $isCli;

    /** @var mixed[][] */
    protected array $matrix;

    /** @var int[] */
    protected array $rows;

    /** @var string[] */
    protected array $columns;

    protected string $gridDisplay;

    protected bool $rowDividers = false;

    protected bool $rowHeaders = true;

    protected bool $columnHeaders = true;

    protected TextGridRightAlign $numbersRight = TextGridRightAlign::none;

    /** @param mixed[][] $matrix */
    public function __construct(array $matrix, bool $isCli = true, bool $rowDividers = false, bool $rowHeaders = true, bool $columnHeaders = true, TextGridRightAlign $numbersRight = TextGridRightAlign::none)
=======
class TextGrid
{
    /**
     * @var bool
     */
    private $isCli = true;

    /**
     * @var array
     */
    protected $matrix;

    /**
     * @var array
     */
    protected $rows;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var string
     */
    private $gridDisplay;

    public function __construct(array $matrix, bool $isCli = true)
>>>>>>> main
    {
        $this->rows = array_keys($matrix);
        $this->columns = array_keys($matrix[$this->rows[0]]);

        $matrix = array_values($matrix);
        array_walk(
            $matrix,
            function (&$row): void {
                $row = array_values($row);
            }
        );

        $this->matrix = $matrix;
        $this->isCli = $isCli;
<<<<<<< HEAD
        $this->rowDividers = $rowDividers;
        $this->rowHeaders = $rowHeaders;
        $this->columnHeaders = $columnHeaders;
        $this->numbersRight = $numbersRight;
    }

    public function setNumbersRight(TextGridRightAlign $numbersRight): void
    {
        $this->numbersRight = $numbersRight;
=======
>>>>>>> main
    }

    public function render(): string
    {
<<<<<<< HEAD
        $this->gridDisplay = $this->isCli ? '' : ('<pre>' . PHP_EOL);

        if (!empty($this->rows)) {
            $maxRow = max($this->rows);
            $maxRowLength = $this->strlen((string) $maxRow) + 1;
            $columnWidths = $this->getColumnWidths();

            $this->renderColumnHeader($maxRowLength, $columnWidths);
            $this->renderRows($maxRowLength, $columnWidths);
            if (!$this->rowDividers) {
                $this->renderFooter($maxRowLength, $columnWidths);
            }
        }
=======
        $this->gridDisplay = $this->isCli ? '' : '<pre>';

        $maxRow = max($this->rows);
        $maxRowLength = mb_strlen((string) $maxRow) + 1;
        $columnWidths = $this->getColumnWidths();

        $this->renderColumnHeader($maxRowLength, $columnWidths);
        $this->renderRows($maxRowLength, $columnWidths);
        $this->renderFooter($maxRowLength, $columnWidths);
>>>>>>> main

        $this->gridDisplay .= $this->isCli ? '' : '</pre>';

        return $this->gridDisplay;
    }

<<<<<<< HEAD
    /** @param int[] $columnWidths */
    protected function renderRows(int $maxRowLength, array $columnWidths): void
    {
        foreach ($this->matrix as $row => $rowData) {
            if ($this->rowHeaders) {
                $this->gridDisplay .= '|' . str_pad((string) $this->rows[$row], $maxRowLength, ' ', STR_PAD_LEFT) . ' ';
            }
            $this->renderCells($rowData, $columnWidths);
            $this->gridDisplay .= '|' . PHP_EOL;
            if ($this->rowDividers) {
                $this->renderFooter($maxRowLength, $columnWidths);
            }
        }
    }

    /**
     * @param mixed[] $rowData
     * @param int[] $columnWidths
     */
    protected function renderCells(array $rowData, array $columnWidths): void
    {
        foreach ($rowData as $column => $cell) {
            $valueForLength = $this->getString($cell);
            $displayCell = $this->isCli ? $valueForLength : htmlentities($valueForLength);
            $this->gridDisplay .= '| ';
            if ($this->rightAlign($displayCell, $cell)) {
                $this->gridDisplay .= str_repeat(' ', $columnWidths[$column] - $this->strlen($valueForLength)) . $displayCell . ' ';
            } else {
                $this->gridDisplay .= $displayCell . str_repeat(' ', $columnWidths[$column] - $this->strlen($valueForLength) + 1);
            }
        }
    }

    protected function rightAlign(string $displayCell, mixed $cell = null): bool
    {
        return ($this->numbersRight === TextGridRightAlign::numeric && is_numeric($displayCell)) || ($this->numbersRight === TextGridRightAlign::floatOrInt && (is_int($cell) || is_float($cell)));
    }

    /** @param int[] $columnWidths */
    protected function renderColumnHeader(int $maxRowLength, array &$columnWidths): void
    {
        if (!$this->columnHeaders) {
            $this->renderFooter($maxRowLength, $columnWidths);

            return;
        }
        foreach ($this->columns as $column => $reference) {
            /** @var string $reference */
            $columnWidths[$column] = max($columnWidths[$column], $this->strlen($reference));
        }
        if ($this->rowHeaders) {
            $this->gridDisplay .= str_repeat(' ', $maxRowLength + 2);
        }
=======
    private function renderRows(int $maxRowLength, array $columnWidths): void
    {
        foreach ($this->matrix as $row => $rowData) {
            $this->gridDisplay .= '|' . str_pad((string) $this->rows[$row], $maxRowLength, ' ', STR_PAD_LEFT) . ' ';
            $this->renderCells($rowData, $columnWidths);
            $this->gridDisplay .= '|' . PHP_EOL;
        }
    }

    private function renderCells(array $rowData, array $columnWidths): void
    {
        foreach ($rowData as $column => $cell) {
            $displayCell = ($this->isCli) ? (string) $cell : htmlentities((string) $cell);
            $this->gridDisplay .= '| ';
            $this->gridDisplay .= $displayCell . str_repeat(' ', $columnWidths[$column] - mb_strlen($cell ?? '') + 1);
        }
    }

    private function renderColumnHeader(int $maxRowLength, array $columnWidths): void
    {
        $this->gridDisplay .= str_repeat(' ', $maxRowLength + 2);
>>>>>>> main
        foreach ($this->columns as $column => $reference) {
            $this->gridDisplay .= '+-' . str_repeat('-', $columnWidths[$column] + 1);
        }
        $this->gridDisplay .= '+' . PHP_EOL;

<<<<<<< HEAD
        if ($this->rowHeaders) {
            $this->gridDisplay .= str_repeat(' ', $maxRowLength + 2);
        }
        foreach ($this->columns as $column => $reference) {
            /** @var scalar $reference */
=======
        $this->gridDisplay .= str_repeat(' ', $maxRowLength + 2);
        foreach ($this->columns as $column => $reference) {
>>>>>>> main
            $this->gridDisplay .= '| ' . str_pad((string) $reference, $columnWidths[$column] + 1, ' ');
        }
        $this->gridDisplay .= '|' . PHP_EOL;

        $this->renderFooter($maxRowLength, $columnWidths);
    }

<<<<<<< HEAD
    /** @param int[] $columnWidths */
    protected function renderFooter(int $maxRowLength, array $columnWidths): void
    {
        if ($this->rowHeaders) {
            $this->gridDisplay .= '+' . str_repeat('-', $maxRowLength + 1);
        }
=======
    private function renderFooter(int $maxRowLength, array $columnWidths): void
    {
        $this->gridDisplay .= '+' . str_repeat('-', $maxRowLength + 1);
>>>>>>> main
        foreach ($this->columns as $column => $reference) {
            $this->gridDisplay .= '+-';
            $this->gridDisplay .= str_pad((string) '', $columnWidths[$column] + 1, '-');
        }
        $this->gridDisplay .= '+' . PHP_EOL;
    }

<<<<<<< HEAD
    /** @return int[] */
    protected function getColumnWidths(): array
=======
    private function getColumnWidths(): array
>>>>>>> main
    {
        $columnCount = count($this->matrix, COUNT_RECURSIVE) / count($this->matrix);
        $columnWidths = [];
        for ($column = 0; $column < $columnCount; ++$column) {
            $columnWidths[] = $this->getColumnWidth(array_column($this->matrix, $column));
        }

        return $columnWidths;
    }

<<<<<<< HEAD
    /** @param mixed[] $columnData */
    protected function getColumnWidth(array $columnData): int
=======
    private function getColumnWidth(array $columnData): int
>>>>>>> main
    {
        $columnWidth = 0;
        $columnData = array_values($columnData);

        foreach ($columnData as $columnValue) {
<<<<<<< HEAD
            $columnWidth = max($columnWidth, $this->strlen($this->getString($columnValue)));
=======
            if (is_string($columnValue)) {
                $columnWidth = max($columnWidth, mb_strlen($columnValue));
            } elseif (is_bool($columnValue)) {
                $columnWidth = max($columnWidth, mb_strlen($columnValue ? 'TRUE' : 'FALSE'));
            }

            $columnWidth = max($columnWidth, mb_strlen((string) $columnWidth));
>>>>>>> main
        }

        return $columnWidth;
    }
<<<<<<< HEAD

    protected function getString(mixed $value): string
    {
        return StringHelper::convertToString($value, convertBool: true);
    }

    protected function strlen(string $value): int
    {
        return mb_strlen($value);
    }
=======
>>>>>>> main
}
