<?php

namespace PhpOffice\PhpSpreadsheet\Reader;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\DefinedName;
use PhpOffice\PhpSpreadsheet\Reader\Gnumeric\PageSetup;
use PhpOffice\PhpSpreadsheet\Reader\Gnumeric\Properties;
use PhpOffice\PhpSpreadsheet\Reader\Gnumeric\Styles;
use PhpOffice\PhpSpreadsheet\Reader\Security\XmlScanner;
use PhpOffice\PhpSpreadsheet\ReferenceHelper;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use SimpleXMLElement;
use XMLReader;

class Gnumeric extends BaseReader
{
    const NAMESPACE_GNM = 'http://www.gnumeric.org/v10.dtd'; // gmr in old sheets

    const NAMESPACE_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    const NAMESPACE_OFFICE = 'urn:oasis:names:tc:opendocument:xmlns:office:1.0';

    const NAMESPACE_XLINK = 'http://www.w3.org/1999/xlink';

    const NAMESPACE_DC = 'http://purl.org/dc/elements/1.1/';

    const NAMESPACE_META = 'urn:oasis:names:tc:opendocument:xmlns:meta:1.0';

    const NAMESPACE_OOO = 'http://openoffice.org/2004/office';

<<<<<<< HEAD
    const GNM_SHEET_VISIBILITY_VISIBLE = 'GNM_SHEET_VISIBILITY_VISIBLE';
    const GNM_SHEET_VISIBILITY_HIDDEN = 'GNM_SHEET_VISIBILITY_HIDDEN';

    /**
     * Shared Expressions.
     *
     * @var array<array{column: int, row: int, formula:string}>
     */
    private array $expressions = [];

    /**
     * Spreadsheet shared across all functions.
     */
    private Spreadsheet $spreadsheet;

    private ReferenceHelper $referenceHelper;

    /** @var array{'dataType': string[]} */
    public static array $mappings = [
=======
    /**
     * Shared Expressions.
     *
     * @var array
     */
    private $expressions = [];

    /**
     * Spreadsheet shared across all functions.
     *
     * @var Spreadsheet
     */
    private $spreadsheet;

    /** @var ReferenceHelper */
    private $referenceHelper;

    /** @var array */
    public static $mappings = [
>>>>>>> main
        'dataType' => [
            '10' => DataType::TYPE_NULL,
            '20' => DataType::TYPE_BOOL,
            '30' => DataType::TYPE_NUMERIC, // Integer doesn't exist in Excel
            '40' => DataType::TYPE_NUMERIC, // Float
            '50' => DataType::TYPE_ERROR,
            '60' => DataType::TYPE_STRING,
            //'70':        //    Cell Range
            //'80':        //    Array
        ],
    ];

    /**
     * Create a new Gnumeric.
     */
    public function __construct()
    {
        parent::__construct();
        $this->referenceHelper = ReferenceHelper::getInstance();
        $this->securityScanner = XmlScanner::getInstance($this);
    }

    /**
     * Can the current IReader read the file?
     */
    public function canRead(string $filename): bool
    {
        $data = null;
        if (File::testFileNoThrow($filename)) {
            $data = $this->gzfileGetContents($filename);
<<<<<<< HEAD
            if (!str_contains($data, self::NAMESPACE_GNM)) {
=======
            if (strpos($data, self::NAMESPACE_GNM) === false) {
>>>>>>> main
                $data = '';
            }
        }

        return !empty($data);
    }

    private static function matchXml(XMLReader $xml, string $expectedLocalName): bool
    {
        return $xml->namespaceURI === self::NAMESPACE_GNM
            && $xml->localName === $expectedLocalName
            && $xml->nodeType === XMLReader::ELEMENT;
    }

    /**
     * Reads names of the worksheets from a file, without parsing the whole file to a Spreadsheet object.
     *
<<<<<<< HEAD
     * @return string[]
     */
    public function listWorksheetNames(string $filename): array
=======
     * @param string $filename
     *
     * @return array
     */
    public function listWorksheetNames($filename)
>>>>>>> main
    {
        File::assertFile($filename);
        if (!$this->canRead($filename)) {
            throw new Exception($filename . ' is an invalid Gnumeric file.');
        }

        $xml = new XMLReader();
        $contents = $this->gzfileGetContents($filename);
        $xml->xml($contents);
        $xml->setParserProperty(2, true);

        $worksheetNames = [];
        while ($xml->read()) {
            if (self::matchXml($xml, 'SheetName')) {
                $xml->read(); //    Move onto the value node
                $worksheetNames[] = (string) $xml->value;
            } elseif (self::matchXml($xml, 'Sheets')) {
                //    break out of the loop once we've got our sheet names rather than parse the entire file
                break;
            }
        }

        return $worksheetNames;
    }

    /**
     * Return worksheet info (Name, Last Column Letter, Last Column Index, Total Rows, Total Columns).
     *
<<<<<<< HEAD
     * @return array<int, array{worksheetName: string, lastColumnLetter: string, lastColumnIndex: int, totalRows: int, totalColumns: int, sheetState: string}>
     */
    public function listWorksheetInfo(string $filename): array
=======
     * @param string $filename
     *
     * @return array
     */
    public function listWorksheetInfo($filename)
>>>>>>> main
    {
        File::assertFile($filename);
        if (!$this->canRead($filename)) {
            throw new Exception($filename . ' is an invalid Gnumeric file.');
        }

        $xml = new XMLReader();
        $contents = $this->gzfileGetContents($filename);
        $xml->xml($contents);
        $xml->setParserProperty(2, true);

        $worksheetInfo = [];
        while ($xml->read()) {
            if (self::matchXml($xml, 'Sheet')) {
                $tmpInfo = [
                    'worksheetName' => '',
                    'lastColumnLetter' => 'A',
                    'lastColumnIndex' => 0,
                    'totalRows' => 0,
                    'totalColumns' => 0,
<<<<<<< HEAD
                    'sheetState' => Worksheet::SHEETSTATE_VISIBLE,
                ];
                $visibility = $xml->getAttribute('Visibility');
                if ((string) $visibility === self::GNM_SHEET_VISIBILITY_HIDDEN) {
                    $tmpInfo['sheetState'] = Worksheet::SHEETSTATE_HIDDEN;
                }
=======
                ];
>>>>>>> main

                while ($xml->read()) {
                    if (self::matchXml($xml, 'Name')) {
                        $xml->read(); //    Move onto the value node
                        $tmpInfo['worksheetName'] = (string) $xml->value;
                    } elseif (self::matchXml($xml, 'MaxCol')) {
                        $xml->read(); //    Move onto the value node
                        $tmpInfo['lastColumnIndex'] = (int) $xml->value;
                        $tmpInfo['totalColumns'] = (int) $xml->value + 1;
                    } elseif (self::matchXml($xml, 'MaxRow')) {
                        $xml->read(); //    Move onto the value node
                        $tmpInfo['totalRows'] = (int) $xml->value + 1;

                        break;
                    }
                }
                $tmpInfo['lastColumnLetter'] = Coordinate::stringFromColumnIndex($tmpInfo['lastColumnIndex'] + 1);
                $worksheetInfo[] = $tmpInfo;
            }
        }

        return $worksheetInfo;
    }

<<<<<<< HEAD
    private function gzfileGetContents(string $filename): string
=======
    /**
     * @param string $filename
     *
     * @return string
     */
    private function gzfileGetContents($filename)
>>>>>>> main
    {
        $data = '';
        $contents = @file_get_contents($filename);
        if ($contents !== false) {
<<<<<<< HEAD
            if (str_starts_with($contents, "\x1f\x8b")) {
=======
            if (substr($contents, 0, 2) === "\x1f\x8b") {
>>>>>>> main
                // Check if gzlib functions are available
                if (function_exists('gzdecode')) {
                    $contents = @gzdecode($contents);
                    if ($contents !== false) {
                        $data = $contents;
                    }
                }
            } else {
                $data = $contents;
            }
        }
        if ($data !== '') {
            $data = $this->getSecurityScannerOrThrow()->scan($data);
        }

        return $data;
    }

<<<<<<< HEAD
    /** @return mixed[] */
=======
>>>>>>> main
    public static function gnumericMappings(): array
    {
        return array_merge(self::$mappings, Styles::$mappings);
    }

    private function processComments(SimpleXMLElement $sheet): void
    {
        if ((!$this->readDataOnly) && (isset($sheet->Objects))) {
            foreach ($sheet->Objects->children(self::NAMESPACE_GNM) as $key => $comment) {
                $commentAttributes = $comment->attributes();
                //    Only comment objects are handled at the moment
                if ($commentAttributes && $commentAttributes->Text) {
                    $this->spreadsheet->getActiveSheet()->getComment((string) $commentAttributes->ObjectBound)
                        ->setAuthor((string) $commentAttributes->Author)
                        ->setText($this->parseRichText((string) $commentAttributes->Text));
                }
            }
        }
    }

<<<<<<< HEAD
    private static function testSimpleXml(mixed $value): SimpleXMLElement
=======
    /**
     * @param mixed $value
     */
    private static function testSimpleXml($value): SimpleXMLElement
>>>>>>> main
    {
        return ($value instanceof SimpleXMLElement) ? $value : new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>');
    }

    /**
     * Loads Spreadsheet from file.
     */
    protected function loadSpreadsheetFromFile(string $filename): Spreadsheet
    {
<<<<<<< HEAD
        $spreadsheet = $this->newSpreadsheet();
        $spreadsheet->setValueBinder($this->valueBinder);
=======
        // Create new Spreadsheet
        $spreadsheet = new Spreadsheet();
>>>>>>> main
        $spreadsheet->removeSheetByIndex(0);

        // Load into this instance
        return $this->loadIntoExisting($filename, $spreadsheet);
    }

    /**
     * Loads from file into Spreadsheet instance.
     */
    public function loadIntoExisting(string $filename, Spreadsheet $spreadsheet): Spreadsheet
    {
        $this->spreadsheet = $spreadsheet;
        File::assertFile($filename);
        if (!$this->canRead($filename)) {
            throw new Exception($filename . ' is an invalid Gnumeric file.');
        }

        $gFileData = $this->gzfileGetContents($filename);

<<<<<<< HEAD
        /** @var XmlScanner */
        $securityScanner = $this->securityScanner;
        $xml2 = simplexml_load_string($securityScanner->scan($gFileData));
=======
        $xml2 = simplexml_load_string($gFileData);
>>>>>>> main
        $xml = self::testSimpleXml($xml2);

        $gnmXML = $xml->children(self::NAMESPACE_GNM);
        (new Properties($this->spreadsheet))->readProperties($xml, $gnmXML);

        $worksheetID = 0;
<<<<<<< HEAD
        $sheetCreated = false;
=======
>>>>>>> main
        foreach ($gnmXML->Sheets->Sheet as $sheetOrNull) {
            $sheet = self::testSimpleXml($sheetOrNull);
            $worksheetName = (string) $sheet->Name;
            if (is_array($this->loadSheetsOnly) && !in_array($worksheetName, $this->loadSheetsOnly, true)) {
                continue;
            }

            $maxRow = $maxCol = 0;

            // Create new Worksheet
            $this->spreadsheet->createSheet();
<<<<<<< HEAD
            $sheetCreated = true;
=======
>>>>>>> main
            $this->spreadsheet->setActiveSheetIndex($worksheetID);
            //    Use false for $updateFormulaCellReferences to prevent adjustment of worksheet references in formula
            //        cells... during the load, all formulae should be correct, and we're simply bringing the worksheet
            //        name in line with the formula, not the reverse
            $this->spreadsheet->getActiveSheet()->setTitle($worksheetName, false, false);

<<<<<<< HEAD
            $visibility = $sheet->attributes()['Visibility'] ?? self::GNM_SHEET_VISIBILITY_VISIBLE;
            if ((string) $visibility !== self::GNM_SHEET_VISIBILITY_VISIBLE) {
=======
            $visibility = $sheet->attributes()['Visibility'] ?? 'GNM_SHEET_VISIBILITY_VISIBLE';
            if ((string) $visibility !== 'GNM_SHEET_VISIBILITY_VISIBLE') {
>>>>>>> main
                $this->spreadsheet->getActiveSheet()->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
            }

            if (!$this->readDataOnly) {
                (new PageSetup($this->spreadsheet))
                    ->printInformation($sheet)
                    ->sheetMargins($sheet);
            }

            foreach ($sheet->Cells->Cell as $cellOrNull) {
                $cell = self::testSimpleXml($cellOrNull);
                $cellAttributes = self::testSimpleXml($cell->attributes());
                $row = (int) $cellAttributes->Row + 1;
                $column = (int) $cellAttributes->Col;

                $maxRow = max($maxRow, $row);
                $maxCol = max($maxCol, $column);

                $column = Coordinate::stringFromColumnIndex($column + 1);

                // Read cell?
<<<<<<< HEAD
                if (!$this->getReadFilter()->readCell($column, $row, $worksheetName)) {
                    continue;
=======
                if ($this->getReadFilter() !== null) {
                    if (!$this->getReadFilter()->readCell($column, $row, $worksheetName)) {
                        continue;
                    }
>>>>>>> main
                }

                $this->loadCell($cell, $worksheetName, $cellAttributes, $column, $row);
            }

            if ($sheet->Styles !== null) {
                (new Styles($this->spreadsheet, $this->readDataOnly))->read($sheet, $maxRow, $maxCol);
            }

            $this->processComments($sheet);
            $this->processColumnWidths($sheet, $maxCol);
            $this->processRowHeights($sheet, $maxRow);
            $this->processMergedCells($sheet);
            $this->processAutofilter($sheet);

            $this->setSelectedCells($sheet);
            ++$worksheetID;
        }
<<<<<<< HEAD
        if ($this->createBlankSheetIfNoneRead && !$sheetCreated) {
            $this->spreadsheet->createSheet();
        }
=======
>>>>>>> main

        $this->processDefinedNames($gnmXML);

        $this->setSelectedSheet($gnmXML);

        // Return
        return $this->spreadsheet;
    }

    private function setSelectedSheet(SimpleXMLElement $gnmXML): void
    {
        if (isset($gnmXML->UIData)) {
            $attributes = self::testSimpleXml($gnmXML->UIData->attributes());
            $selectedSheet = (int) $attributes['SelectedTab'];
            $this->spreadsheet->setActiveSheetIndex($selectedSheet);
        }
    }

    private function setSelectedCells(?SimpleXMLElement $sheet): void
    {
        if ($sheet !== null && isset($sheet->Selections)) {
            foreach ($sheet->Selections as $selection) {
                $startCol = (int) ($selection->StartCol ?? 0);
                $startRow = (int) ($selection->StartRow ?? 0) + 1;
                $endCol = (int) ($selection->EndCol ?? $startCol);
                $endRow = (int) ($selection->endRow ?? 0) + 1;

                $startColumn = Coordinate::stringFromColumnIndex($startCol + 1);
                $endColumn = Coordinate::stringFromColumnIndex($endCol + 1);

                $startCell = "{$startColumn}{$startRow}";
                $endCell = "{$endColumn}{$endRow}";
                $selectedRange = $startCell . (($endCell !== $startCell) ? ':' . $endCell : '');
                $this->spreadsheet->getActiveSheet()->setSelectedCell($selectedRange);

                break;
            }
        }
    }

    private function processMergedCells(?SimpleXMLElement $sheet): void
    {
        //    Handle Merged Cells in this worksheet
        if ($sheet !== null && isset($sheet->MergedRegions)) {
            foreach ($sheet->MergedRegions->Merge as $mergeCells) {
<<<<<<< HEAD
                if (str_contains((string) $mergeCells, ':')) {
=======
                if (strpos((string) $mergeCells, ':') !== false) {
>>>>>>> main
                    $this->spreadsheet->getActiveSheet()->mergeCells($mergeCells, Worksheet::MERGE_CELL_CONTENT_HIDE);
                }
            }
        }
    }

    private function processAutofilter(?SimpleXMLElement $sheet): void
    {
        if ($sheet !== null && isset($sheet->Filters)) {
            foreach ($sheet->Filters->Filter as $autofilter) {
<<<<<<< HEAD
                $attributes = $autofilter->attributes();
                if (isset($attributes['Area'])) {
                    $this->spreadsheet->getActiveSheet()->setAutoFilter((string) $attributes['Area']);
=======
                if ($autofilter !== null) {
                    $attributes = $autofilter->attributes();
                    if (isset($attributes['Area'])) {
                        $this->spreadsheet->getActiveSheet()->setAutoFilter((string) $attributes['Area']);
                    }
>>>>>>> main
                }
            }
        }
    }

    private function setColumnWidth(int $whichColumn, float $defaultWidth): void
    {
<<<<<<< HEAD
        $this->spreadsheet->getActiveSheet()
            ->getColumnDimension(
                Coordinate::stringFromColumnIndex($whichColumn + 1)
            )
            ->setWidth($defaultWidth);
=======
        $columnDimension = $this->spreadsheet->getActiveSheet()
            ->getColumnDimension(Coordinate::stringFromColumnIndex($whichColumn + 1));
        if ($columnDimension !== null) {
            $columnDimension->setWidth($defaultWidth);
        }
>>>>>>> main
    }

    private function setColumnInvisible(int $whichColumn): void
    {
<<<<<<< HEAD
        $this->spreadsheet->getActiveSheet()
            ->getColumnDimension(
                Coordinate::stringFromColumnIndex($whichColumn + 1)
            )
            ->setVisible(false);
=======
        $columnDimension = $this->spreadsheet->getActiveSheet()
            ->getColumnDimension(Coordinate::stringFromColumnIndex($whichColumn + 1));
        if ($columnDimension !== null) {
            $columnDimension->setVisible(false);
        }
>>>>>>> main
    }

    private function processColumnLoop(int $whichColumn, int $maxCol, ?SimpleXMLElement $columnOverride, float $defaultWidth): int
    {
        $columnOverride = self::testSimpleXml($columnOverride);
        $columnAttributes = self::testSimpleXml($columnOverride->attributes());
        $column = $columnAttributes['No'];
        $columnWidth = ((float) $columnAttributes['Unit']) / 5.4;
        $hidden = (isset($columnAttributes['Hidden'])) && ((string) $columnAttributes['Hidden'] == '1');
        $columnCount = (int) ($columnAttributes['Count'] ?? 1);
        while ($whichColumn < $column) {
            $this->setColumnWidth($whichColumn, $defaultWidth);
            ++$whichColumn;
        }
        while (($whichColumn < ($column + $columnCount)) && ($whichColumn <= $maxCol)) {
            $this->setColumnWidth($whichColumn, $columnWidth);
            if ($hidden) {
                $this->setColumnInvisible($whichColumn);
            }
            ++$whichColumn;
        }

        return $whichColumn;
    }

    private function processColumnWidths(?SimpleXMLElement $sheet, int $maxCol): void
    {
        if ((!$this->readDataOnly) && $sheet !== null && (isset($sheet->Cols))) {
            //    Column Widths
            $defaultWidth = 0;
            $columnAttributes = $sheet->Cols->attributes();
            if ($columnAttributes !== null) {
                $defaultWidth = $columnAttributes['DefaultSizePts'] / 5.4;
            }
            $whichColumn = 0;
            foreach ($sheet->Cols->ColInfo as $columnOverride) {
                $whichColumn = $this->processColumnLoop($whichColumn, $maxCol, $columnOverride, $defaultWidth);
            }
            while ($whichColumn <= $maxCol) {
                $this->setColumnWidth($whichColumn, $defaultWidth);
                ++$whichColumn;
            }
        }
    }

    private function setRowHeight(int $whichRow, float $defaultHeight): void
    {
<<<<<<< HEAD
        $this->spreadsheet
            ->getActiveSheet()
            ->getRowDimension($whichRow)
            ->setRowHeight($defaultHeight);
=======
        $rowDimension = $this->spreadsheet->getActiveSheet()->getRowDimension($whichRow);
        if ($rowDimension !== null) {
            $rowDimension->setRowHeight($defaultHeight);
        }
>>>>>>> main
    }

    private function setRowInvisible(int $whichRow): void
    {
<<<<<<< HEAD
        $this->spreadsheet
            ->getActiveSheet()
            ->getRowDimension($whichRow)
            ->setVisible(false);
=======
        $rowDimension = $this->spreadsheet->getActiveSheet()->getRowDimension($whichRow);
        if ($rowDimension !== null) {
            $rowDimension->setVisible(false);
        }
>>>>>>> main
    }

    private function processRowLoop(int $whichRow, int $maxRow, ?SimpleXMLElement $rowOverride, float $defaultHeight): int
    {
        $rowOverride = self::testSimpleXml($rowOverride);
        $rowAttributes = self::testSimpleXml($rowOverride->attributes());
        $row = $rowAttributes['No'];
        $rowHeight = (float) $rowAttributes['Unit'];
        $hidden = (isset($rowAttributes['Hidden'])) && ((string) $rowAttributes['Hidden'] == '1');
        $rowCount = (int) ($rowAttributes['Count'] ?? 1);
        while ($whichRow < $row) {
            ++$whichRow;
            $this->setRowHeight($whichRow, $defaultHeight);
        }
        while (($whichRow < ($row + $rowCount)) && ($whichRow < $maxRow)) {
            ++$whichRow;
            $this->setRowHeight($whichRow, $rowHeight);
            if ($hidden) {
                $this->setRowInvisible($whichRow);
            }
        }

        return $whichRow;
    }

    private function processRowHeights(?SimpleXMLElement $sheet, int $maxRow): void
    {
        if ((!$this->readDataOnly) && $sheet !== null && (isset($sheet->Rows))) {
            //    Row Heights
            $defaultHeight = 0;
            $rowAttributes = $sheet->Rows->attributes();
            if ($rowAttributes !== null) {
                $defaultHeight = (float) $rowAttributes['DefaultSizePts'];
            }
            $whichRow = 0;

            foreach ($sheet->Rows->RowInfo as $rowOverride) {
                $whichRow = $this->processRowLoop($whichRow, $maxRow, $rowOverride, $defaultHeight);
            }
            // never executed, I can't figure out any circumstances
            // under which it would be executed, and, even if
            // such exist, I'm not convinced this is needed.
            //while ($whichRow < $maxRow) {
            //    ++$whichRow;
            //    $this->spreadsheet->getActiveSheet()->getRowDimension($whichRow)->setRowHeight($defaultHeight);
            //}
        }
    }

    private function processDefinedNames(?SimpleXMLElement $gnmXML): void
    {
        //    Loop through definedNames (global named ranges)
        if ($gnmXML !== null && isset($gnmXML->Names)) {
            foreach ($gnmXML->Names->Name as $definedName) {
                $name = (string) $definedName->name;
                $value = (string) $definedName->value;
<<<<<<< HEAD
                if (stripos($value, '#REF!') !== false || empty($value)) {
                    continue;
                }

                $value = str_replace("\\'", "''", $value);
                [$worksheetName] = Worksheet::extractSheetTitle($value, true, true);
=======
                if (stripos($value, '#REF!') !== false) {
                    continue;
                }

                [$worksheetName] = Worksheet::extractSheetTitle($value, true);
                $worksheetName = trim($worksheetName, "'");
>>>>>>> main
                $worksheet = $this->spreadsheet->getSheetByName($worksheetName);
                // Worksheet might still be null if we're only loading selected sheets rather than the full spreadsheet
                if ($worksheet !== null) {
                    $this->spreadsheet->addDefinedName(DefinedName::createInstance($name, $worksheet, $value));
                }
            }
        }
    }

    private function parseRichText(string $is): RichText
    {
        $value = new RichText();
        $value->createText($is);

        return $value;
    }

    private function loadCell(
        SimpleXMLElement $cell,
        string $worksheetName,
        SimpleXMLElement $cellAttributes,
        string $column,
        int $row
    ): void {
        $ValueType = $cellAttributes->ValueType;
        $ExprID = (string) $cellAttributes->ExprID;
<<<<<<< HEAD
        $rows = (int) ($cellAttributes->Rows ?? 0);
        $cols = (int) ($cellAttributes->Cols ?? 0);
        $type = DataType::TYPE_FORMULA;
        $isArrayFormula = ($rows > 0 && $cols > 0);
        $arrayFormulaRange = $isArrayFormula ? $this->getArrayFormulaRange($column, $row, $cols, $rows) : null;
        if ($ExprID > '') {
            if (((string) $cell) > '') {
                // Formula
                $this->expressions[$ExprID] = [
                    'column' => (int) $cellAttributes->Col,
                    'row' => (int) $cellAttributes->Row,
                    'formula' => (string) $cell,
                ];
            } else {
                // Shared Formula
=======
        $type = DataType::TYPE_FORMULA;
        if ($ExprID > '') {
            if (((string) $cell) > '') {
                $this->expressions[$ExprID] = [
                    'column' => $cellAttributes->Col,
                    'row' => $cellAttributes->Row,
                    'formula' => (string) $cell,
                ];
            } else {
>>>>>>> main
                $expression = $this->expressions[$ExprID];

                $cell = $this->referenceHelper->updateFormulaReferences(
                    $expression['formula'],
                    'A1',
                    $cellAttributes->Col - $expression['column'],
                    $cellAttributes->Row - $expression['row'],
                    $worksheetName
                );
            }
            $type = DataType::TYPE_FORMULA;
<<<<<<< HEAD
        } elseif ($isArrayFormula === false) {
=======
        } else {
>>>>>>> main
            $vtype = (string) $ValueType;
            if (array_key_exists($vtype, self::$mappings['dataType'])) {
                $type = self::$mappings['dataType'][$vtype];
            }
<<<<<<< HEAD
            if ($vtype === '20') { //    Boolean
=======
            if ($vtype === '20') {        //    Boolean
>>>>>>> main
                $cell = $cell == 'TRUE';
            }
        }

        $this->spreadsheet->getActiveSheet()->getCell($column . $row)->setValueExplicit((string) $cell, $type);
<<<<<<< HEAD
        if ($arrayFormulaRange === null) {
            $this->spreadsheet->getActiveSheet()->getCell($column . $row)->setFormulaAttributes(null);
        } else {
            $this->spreadsheet->getActiveSheet()->getCell($column . $row)->setFormulaAttributes(['t' => 'array', 'ref' => $arrayFormulaRange]);
        }
=======
>>>>>>> main
        if (isset($cellAttributes->ValueFormat)) {
            $this->spreadsheet->getActiveSheet()->getCell($column . $row)
                ->getStyle()->getNumberFormat()
                ->setFormatCode((string) $cellAttributes->ValueFormat);
        }
    }
<<<<<<< HEAD

    private function getArrayFormulaRange(string $column, int $row, int $cols, int $rows): string
    {
        $arrayFormulaRange = $column . $row;
        $arrayFormulaRange .= ':'
            . Coordinate::stringFromColumnIndex(
                Coordinate::columnIndexFromString($column)
                + $cols - 1
            )
            . (string) ($row + $rows - 1);

        return $arrayFormulaRange;
    }
=======
>>>>>>> main
}
