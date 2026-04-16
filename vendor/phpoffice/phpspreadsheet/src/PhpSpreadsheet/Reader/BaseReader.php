<?php

namespace PhpOffice\PhpSpreadsheet\Reader;

use Closure;
<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Cell\IValueBinder;
=======
>>>>>>> main
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Reader\Security\XmlScanner;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

abstract class BaseReader implements IReader
{
    /**
     * Read data only?
     * Identifies whether the Reader should only read data values for cells, and ignore any formatting information;
     *        or whether it should read both data and formatting.
<<<<<<< HEAD
     */
    protected bool $readDataOnly = false;

    /**
     * Read empty cells?
     * Identifies whether the Reader should read data values for all cells, or should ignore cells containing
     *         null value or empty string.
     */
    protected bool $readEmptyCells = true;
=======
     *
     * @var bool
     */
    protected $readDataOnly = false;

    /**
     * Read empty cells?
     * Identifies whether the Reader should read data values for cells all cells, or should ignore cells containing
     *         null value or empty string.
     *
     * @var bool
     */
    protected $readEmptyCells = true;
>>>>>>> main

    /**
     * Read charts that are defined in the workbook?
     * Identifies whether the Reader should read the definitions for any charts that exist in the workbook;.
<<<<<<< HEAD
     */
    protected bool $includeCharts = false;
=======
     *
     * @var bool
     */
    protected $includeCharts = false;
>>>>>>> main

    /**
     * Restrict which sheets should be loaded?
     * This property holds an array of worksheet names to be loaded. If null, then all worksheets will be loaded.
<<<<<<< HEAD
     * This property is ignored for Csv, Html, and Slk.
     *
     * @var null|string[]
     */
    protected ?array $loadSheetsOnly = null;

    /**
     * Ignore rows with no cells?
     * Identifies whether the Reader should ignore rows with no cells.
     *        Currently implemented only for Xlsx.
     */
    protected bool $ignoreRowsWithNoCells = false;
=======
     *
     * @var null|string[]
     */
    protected $loadSheetsOnly;
>>>>>>> main

    /**
     * Allow external images. Use with caution.
     * Improper specification of these within a spreadsheet
     * can subject the caller to security exploits.
<<<<<<< HEAD
     */
    protected bool $allowExternalImages = false;

    /**
     * Create a blank sheet if none are read,
     * possibly due to a typo when using LoadSheetsOnly.
     */
    protected bool $createBlankSheetIfNoneRead = false;

    /**
     * Enable drawing pass-through?
     * Identifies whether the Reader should preserve unsupported drawing elements (shapes, grouped images, etc.)
     * by storing the original XML for pass-through during write operations.
     * When enabled, drawings cannot be modified programmatically but are preserved exactly.
     */
    protected bool $enableDrawingPassThrough = false;

    /**
     * IReadFilter instance.
     */
    protected IReadFilter $readFilter;
=======
     *
     * @var bool
     */
    protected $allowExternalImages = false;

    /**
     * IReadFilter instance.
     *
     * @var IReadFilter
     */
    protected $readFilter;
>>>>>>> main

    /** @var resource */
    protected $fileHandle;

<<<<<<< HEAD
    protected ?XmlScanner $securityScanner = null;

    protected ?IValueBinder $valueBinder = null;
=======
    /**
     * @var ?XmlScanner
     */
    protected $securityScanner;
>>>>>>> main

    /** @var null|Closure(string):bool function to return whether image path is okay */
    protected ?Closure $isWhitelisted = null;

    public function __construct()
    {
        $this->readFilter = new DefaultReadFilter();
    }

<<<<<<< HEAD
    public function getReadDataOnly(): bool
=======
    public function getReadDataOnly()
>>>>>>> main
    {
        return $this->readDataOnly;
    }

<<<<<<< HEAD
    public function setReadDataOnly(bool $readCellValuesOnly): self
    {
        $this->readDataOnly = $readCellValuesOnly;
=======
    public function setReadDataOnly($readCellValuesOnly)
    {
        $this->readDataOnly = (bool) $readCellValuesOnly;
>>>>>>> main

        return $this;
    }

<<<<<<< HEAD
    public function getReadEmptyCells(): bool
=======
    public function getReadEmptyCells()
>>>>>>> main
    {
        return $this->readEmptyCells;
    }

<<<<<<< HEAD
    public function setReadEmptyCells(bool $readEmptyCells): self
    {
        $this->readEmptyCells = $readEmptyCells;
=======
    public function setReadEmptyCells($readEmptyCells)
    {
        $this->readEmptyCells = (bool) $readEmptyCells;
>>>>>>> main

        return $this;
    }

<<<<<<< HEAD
    public function getIgnoreRowsWithNoCells(): bool
    {
        return $this->ignoreRowsWithNoCells;
    }

    public function setIgnoreRowsWithNoCells(bool $ignoreRowsWithNoCells): self
    {
        $this->ignoreRowsWithNoCells = $ignoreRowsWithNoCells;

        return $this;
    }

    public function getIncludeCharts(): bool
=======
    public function getIncludeCharts()
>>>>>>> main
    {
        return $this->includeCharts;
    }

<<<<<<< HEAD
    public function setIncludeCharts(bool $includeCharts): self
    {
        $this->includeCharts = $includeCharts;
=======
    public function setIncludeCharts($includeCharts)
    {
        $this->includeCharts = (bool) $includeCharts;
>>>>>>> main

        return $this;
    }

<<<<<<< HEAD
    public function getEnableDrawingPassThrough(): bool
    {
        return $this->enableDrawingPassThrough;
    }

    public function setEnableDrawingPassThrough(bool $enableDrawingPassThrough): self
    {
        $this->enableDrawingPassThrough = $enableDrawingPassThrough;

        return $this;
    }

    /** @return null|string[] */
    public function getLoadSheetsOnly(): ?array
=======
    public function getLoadSheetsOnly()
>>>>>>> main
    {
        return $this->loadSheetsOnly;
    }

<<<<<<< HEAD
    /** @param null|string|string[] $sheetList */
    public function setLoadSheetsOnly(string|array|null $sheetList): self
=======
    public function setLoadSheetsOnly($sheetList)
>>>>>>> main
    {
        if ($sheetList === null) {
            return $this->setLoadAllSheets();
        }

        $this->loadSheetsOnly = is_array($sheetList) ? $sheetList : [$sheetList];

        return $this;
    }

<<<<<<< HEAD
    public function setLoadAllSheets(): self
=======
    public function setLoadAllSheets()
>>>>>>> main
    {
        $this->loadSheetsOnly = null;

        return $this;
    }

<<<<<<< HEAD
    public function getReadFilter(): IReadFilter
=======
    public function getReadFilter()
>>>>>>> main
    {
        return $this->readFilter;
    }

<<<<<<< HEAD
    public function setReadFilter(IReadFilter $readFilter): self
=======
    public function setReadFilter(IReadFilter $readFilter)
>>>>>>> main
    {
        $this->readFilter = $readFilter;

        return $this;
    }

<<<<<<< HEAD
    /**
     * USE WITH CAUTION (and in conjunction with setIsWhiteListed)!
     * Allow external images;
     * these can be specified within a spreadsheet
     * in a way that can subject the caller to security exploits.
     */
    public function setAllowExternalImages(bool $allowExternalImages): self
    {
        $this->allowExternalImages = $allowExternalImages;

        return $this;
    }

    public function getAllowExternalImages(): bool
    {
        return $this->allowExternalImages;
    }

    /**
     * USE WITH CAUTION!
     * Supply a callback to determine whether a path should be whitelisted,
     * used in conjunction with setAllowExternalImages;
     * supplying a method which might return true
     * can subject the caller to security exploits.
     *
     * @param Closure(string):bool $isWhitelisted
     */
    public function setIsWhitelisted(Closure $isWhitelisted): self
    {
        $this->isWhitelisted = $isWhitelisted;

        return $this;
    }

    /**
     * Create a blank sheet if none are read,
     * possibly due to a typo when using LoadSheetsOnly.
     */
    public function setCreateBlankSheetIfNoneRead(bool $createBlankSheetIfNoneRead): self
    {
        $this->createBlankSheetIfNoneRead = $createBlankSheetIfNoneRead;

        return $this;
    }

=======
>>>>>>> main
    public function getSecurityScanner(): ?XmlScanner
    {
        return $this->securityScanner;
    }

    public function getSecurityScannerOrThrow(): XmlScanner
    {
        if ($this->securityScanner === null) {
            throw new ReaderException('Security scanner is unexpectedly null');
        }

        return $this->securityScanner;
    }

    protected function processFlags(int $flags): void
    {
        if (((bool) ($flags & self::LOAD_WITH_CHARTS)) === true) {
            $this->setIncludeCharts(true);
        }
        if (((bool) ($flags & self::READ_DATA_ONLY)) === true) {
            $this->setReadDataOnly(true);
        }
<<<<<<< HEAD
        if (((bool) ($flags & self::IGNORE_EMPTY_CELLS)) === true) {
            $this->setReadEmptyCells(false);
        }
        if (((bool) ($flags & self::IGNORE_ROWS_WITH_NO_CELLS)) === true) {
            $this->setIgnoreRowsWithNoCells(true);
        }
=======
        if (((bool) ($flags & self::SKIP_EMPTY_CELLS) || (bool) ($flags & self::IGNORE_EMPTY_CELLS)) === true) {
            $this->setReadEmptyCells(false);
        }
>>>>>>> main
        if (((bool) ($flags & self::ALLOW_EXTERNAL_IMAGES)) === true) {
            $this->setAllowExternalImages(true);
        }
        if (((bool) ($flags & self::DONT_ALLOW_EXTERNAL_IMAGES)) === true) {
            $this->setAllowExternalImages(false);
        }
<<<<<<< HEAD
        if (((bool) ($flags & self::CREATE_BLANK_SHEET_IF_NONE_READ)) === true) {
            $this->setCreateBlankSheetIfNoneRead(true);
        }
=======
>>>>>>> main
    }

    protected function loadSpreadsheetFromFile(string $filename): Spreadsheet
    {
        throw new PhpSpreadsheetException('Reader classes must implement their own loadSpreadsheetFromFile() method');
    }

    /**
     * Loads Spreadsheet from file.
     *
     * @param int $flags the optional second parameter flags may be used to identify specific elements
     *                       that should be loaded, but which won't be loaded by default, using these values:
     *                            IReader::LOAD_WITH_CHARTS - Include any charts that are defined in the loaded file
     */
    public function load(string $filename, int $flags = 0): Spreadsheet
    {
        $this->processFlags($flags);

        try {
            return $this->loadSpreadsheetFromFile($filename);
        } catch (ReaderException $e) {
            throw $e;
        }
    }

    /**
     * Open file for reading.
     */
    protected function openFile(string $filename): void
    {
        $fileHandle = false;
        if ($filename) {
            File::assertFile($filename);

            // Open file
            $fileHandle = fopen($filename, 'rb');
        }
        if ($fileHandle === false) {
            throw new ReaderException('Could not open file ' . $filename . ' for reading.');
        }

        $this->fileHandle = $fileHandle;
    }

    /**
<<<<<<< HEAD
     * Return worksheet info (Name, Last Column Letter, Last Column Index, Total Rows, Total Columns).
     *
     * @return array<int, array{worksheetName: string, lastColumnLetter: string, lastColumnIndex: int, totalRows: int, totalColumns: int, sheetState: string}>
     */
    public function listWorksheetInfo(string $filename): array
    {
        throw new PhpSpreadsheetException('Reader classes must implement their own listWorksheetInfo() method');
    }

    /**
     * Returns names of the worksheets from a file,
     * possibly without parsing the whole file to a Spreadsheet object.
     * Readers will often have a more efficient method with which
     * they can override this method.
     *
     * @return string[]
     */
    public function listWorksheetNames(string $filename): array
    {
        $returnArray = [];
        $info = $this->listWorksheetInfo($filename);
        foreach ($info as $infoArray) {
            $returnArray[] = $infoArray['worksheetName'];
        }

        return $returnArray;
    }

    public function getValueBinder(): ?IValueBinder
    {
        return $this->valueBinder;
    }

    public function setValueBinder(?IValueBinder $valueBinder): self
    {
        $this->valueBinder = $valueBinder;
=======
     * USE WITH CAUTION (and in conjunction with setIsWhiteListed)!
     * Allow external images;
     * these can be specified within a spreadsheet
     * in a way that can subject the caller to security exploits.
     */
    public function setAllowExternalImages(bool $allowExternalImages)
    {
        $this->allowExternalImages = $allowExternalImages;
>>>>>>> main

        return $this;
    }

<<<<<<< HEAD
    protected function newSpreadsheet(): Spreadsheet
    {
        return new Spreadsheet();
=======
    public function getAllowExternalImages()
    {
        return $this->allowExternalImages;
    }

    /**
     * USE WITH CAUTION!
     * Supply a callback to determine whether a path should be whitelisted,
     * used in conjunction with setAllowExternalImages;
     * supplying a method which might return true
     * can subject the caller to security exploits.
     *
     * @param Closure(string):bool $isWhitelisted
     */
    public function setIsWhitelisted(Closure $isWhitelisted): self
    {
        $this->isWhitelisted = $isWhitelisted;

        return $this;
>>>>>>> main
    }
}
