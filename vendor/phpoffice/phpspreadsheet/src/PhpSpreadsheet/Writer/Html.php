<?php

namespace PhpOffice\PhpSpreadsheet\Writer;

<<<<<<< HEAD
use Composer\Pcre\Preg;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Calculation\Exception as CalculationException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Comment;
=======
use HTMLPurifier;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
>>>>>>> main
use PhpOffice\PhpSpreadsheet\Document\Properties;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Shared\Drawing as SharedDrawing;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Shared\Font as SharedFont;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Borders;
<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\MergedCellStyle;
=======
>>>>>>> main
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Style;
<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
=======
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
>>>>>>> main
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Html extends BaseWriter
{
<<<<<<< HEAD
    private const DEFAULT_CELL_WIDTH_POINTS = 42;

    private const DEFAULT_CELL_WIDTH_PIXELS = 56;

    /**
     * Migration aid to tell if html tags will be treated as plaintext in comments.
     *     if (
     *         defined(
     *             \PhpOffice\PhpSpreadsheet\Writer\Html::class
     *             . '::COMMENT_HTML_TAGS_PLAINTEXT'
     *         )
     *     ) {
     *         new logic with styling in TextRun elements
     *     } else {
     *         old logic with styling via Html tags
     *     }.
     */
    public const COMMENT_HTML_TAGS_PLAINTEXT = true;

    private const BRX = '<br          />';

    /**
     * Spreadsheet object.
     */
    protected Spreadsheet $spreadsheet;

    /**
     * Sheet index to write.
     */
    private ?int $sheetIndex = 0;

    /**
     * Images root.
     */
    private string $imagesRoot = '';

    /**
     * embed images, or link to images.
     */
    protected bool $embedImages = false;

    protected string $lineEnding = PHP_EOL;

    public function getLineEnding(): string
    {
        return $this->lineEnding;
    }

    public function setLineEnding(string $lineEnding): self
    {
        if ($lineEnding != "\n" && $lineEnding !== "\r\n") {
            throw new Exception('Line ending must be \n (Unix) or \r\n (Windows)');
        }
        $this->lineEnding = $lineEnding;

        return $this;
    }

    protected bool $dataFormula = false;

    public function setDataFormula(bool $dataFormula): self
    {
        $this->dataFormula = $dataFormula;

        return $this;
    }

    /**
     * Use inline CSS?
     */
    private bool $useInlineCss = false;
=======
    /**
     * Spreadsheet object.
     *
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     * Sheet index to write.
     *
     * @var null|int
     */
    private $sheetIndex = 0;

    /**
     * Images root.
     *
     * @var string
     */
    private $imagesRoot = '';

    /**
     * embed images, or link to images.
     *
     * @var bool
     */
    protected $embedImages = false;

    /**
     * Use inline CSS?
     *
     * @var bool
     */
    private $useInlineCss = false;

    /**
     * Use embedded CSS?
     *
     * @var bool
     */
    private $useEmbeddedCSS = true;
>>>>>>> main

    /**
     * Array of CSS styles.
     *
<<<<<<< HEAD
     * @var string[][]
     */
    private ?array $cssStyles = null;
=======
     * @var array
     */
    private $cssStyles;
>>>>>>> main

    /**
     * Array of column widths in points.
     *
<<<<<<< HEAD
     * @var array<array<float|int>>
     */
    private array $columnWidths;

    /**
     * Default font.
     */
    private Font $defaultFont;

    /**
     * Flag whether spans have been calculated.
     */
    private bool $spansAreCalculated = false;
=======
     * @var array
     */
    private $columnWidths;

    /**
     * Default font.
     *
     * @var Font
     */
    private $defaultFont;

    /**
     * Flag whether spans have been calculated.
     *
     * @var bool
     */
    private $spansAreCalculated = false;
>>>>>>> main

    /**
     * Excel cells that should not be written as HTML cells.
     *
<<<<<<< HEAD
     * @var mixed[][][][]
     */
    private array $isSpannedCell = [];
=======
     * @var array
     */
    private $isSpannedCell = [];
>>>>>>> main

    /**
     * Excel cells that are upper-left corner in a cell merge.
     *
<<<<<<< HEAD
     * @var int[][][][]
     */
    private array $isBaseCell = [];

    /**
     * Is the current writer creating PDF?
     */
    protected bool $isPdf = false;

    /**
     * Generate the Navigation block.
     */
    private bool $generateSheetNavigationBlock = true;
=======
     * @var array
     */
    private $isBaseCell = [];

    /**
     * Excel rows that should not be written as HTML rows.
     *
     * @var array
     */
    private $isSpannedRow = [];

    /**
     * Is the current writer creating PDF?
     *
     * @var bool
     */
    protected $isPdf = false;

    /**
     * Is the current writer creating mPDF?
     *
     * @var bool
     */
    protected $isMPdf = false;

    /**
     * Generate the Navigation block.
     *
     * @var bool
     */
    private $generateSheetNavigationBlock = true;
>>>>>>> main

    /**
     * Callback for editing generated html.
     *
<<<<<<< HEAD
     * @var null|callable(string): string
     */
    private $editHtmlCallback;

    /** @var BaseDrawing[] */
    private $sheetDrawings;

    /** @var Chart[] */
    private $sheetCharts;

    private bool $betterBoolean = true;

    private string $getTrue = 'TRUE';

    private string $getFalse = 'FALSE';

    protected bool $rtlSheets = false;

    protected bool $ltrSheets = false;

    /**
     * Table formats
     * Enables table formats in writer, disabled here, must be enabled in writer via a setter.
     */
    protected bool $tableFormats = false;

    /**
     * Table formats for unstyled tables.
     * Enables default style for builtin table formats.
     * If null, it takes on the same value as $tableFormats.
     */
    protected ?bool $tableFormatsBuiltin = null;

    /**
     * Conditional Formatting
     * Enables conditional formatting in writer, disabled here, must be enabled in writer via a setter.
     */
    protected bool $conditionalFormatting = false;

=======
     * @var null|callable
     */
    private $editHtmlCallback;

>>>>>>> main
    /**
     * Create a new HTML.
     */
    public function __construct(Spreadsheet $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
        $this->defaultFont = $this->spreadsheet->getDefaultStyle()->getFont();
<<<<<<< HEAD
        $calc = Calculation::getInstance($this->spreadsheet);
        $this->getTrue = $calc->getTRUE();
        $this->getFalse = $calc->getFALSE();
=======
>>>>>>> main
    }

    /**
     * Save Spreadsheet to file.
     *
     * @param resource|string $filename
     */
    public function save($filename, int $flags = 0): void
    {
        $this->processFlags($flags);
<<<<<<< HEAD
        // Open file
        $this->openFileHandle($filename);
        // Write html
        fwrite($this->fileHandle, $this->generateHTMLAll());
=======

        // Open file
        $this->openFileHandle($filename);

        // Write html
        fwrite($this->fileHandle, $this->generateHTMLAll());

>>>>>>> main
        // Close file
        $this->maybeCloseFileHandle();
    }

<<<<<<< HEAD
    protected function checkRtlAndLtr(): void
    {
        $this->rtlSheets = false;
        $this->ltrSheets = false;
        if ($this->sheetIndex === null) {
            foreach ($this->spreadsheet->getAllSheets() as $sheet) {
                if ($sheet->getRightToLeft()) {
                    $this->rtlSheets = true;
                } else {
                    $this->ltrSheets = true;
                }
            }
        } else {
            if ($this->spreadsheet->getSheet($this->sheetIndex)->getRightToLeft()) {
                $this->rtlSheets = true;
            }
        }
    }

    /**
     * Save Spreadsheet as html to variable.
     */
    public function generateHtmlAll(): string
    {
        $this->checkRtlAndLtr();
        $sheets = $this->generateSheetPrep();
        foreach ($sheets as $sheet) {
            $sheet->calculateArrays($this->preCalculateFormulas);
        }
=======
    /**
     * Save Spreadsheet as html to variable.
     *
     * @return string
     */
    public function generateHtmlAll()
    {
>>>>>>> main
        // garbage collect
        $this->spreadsheet->garbageCollect();

        $saveDebugLog = Calculation::getInstance($this->spreadsheet)->getDebugLog()->getWriteDebugLog();
        Calculation::getInstance($this->spreadsheet)->getDebugLog()->setWriteDebugLog(false);
<<<<<<< HEAD
=======
        $saveArrayReturnType = Calculation::getArrayReturnType();
        Calculation::setArrayReturnType(Calculation::RETURN_ARRAY_AS_VALUE);
>>>>>>> main

        // Build CSS
        $this->buildCSS(!$this->useInlineCss);

        $html = '';

        // Write headers
        $html .= $this->generateHTMLHeader(!$this->useInlineCss);

        // Write navigation (tabs)
        if ((!$this->isPdf) && ($this->generateSheetNavigationBlock)) {
            $html .= $this->generateNavigation();
        }

        // Write data
        $html .= $this->generateSheetData();

        // Write footer
        $html .= $this->generateHTMLFooter();
<<<<<<< HEAD
        if ($this instanceof Pdf\Mpdf) {
            $html = str_replace(self::BRX, '<br />', $html);
        } else {
            $html = str_replace(self::BRX, '<br />' . $this->lineEnding, $html);
        }
=======
>>>>>>> main
        $callback = $this->editHtmlCallback;
        if ($callback) {
            $html = $callback($html);
        }

<<<<<<< HEAD
=======
        Calculation::setArrayReturnType($saveArrayReturnType);
>>>>>>> main
        Calculation::getInstance($this->spreadsheet)->getDebugLog()->setWriteDebugLog($saveDebugLog);

        return $html;
    }

    /**
     * Set a callback to edit the entire HTML.
     *
     * The callback must accept the HTML as string as first parameter,
     * and it must return the edited HTML as string.
     */
    public function setEditHtmlCallback(?callable $callback): void
    {
        $this->editHtmlCallback = $callback;
    }

    /**
     * Map VAlign.
     *
     * @param string $vAlign Vertical alignment
<<<<<<< HEAD
     */
    private function mapVAlign(string $vAlign): string
=======
     *
     * @return string
     */
    private function mapVAlign($vAlign)
>>>>>>> main
    {
        return Alignment::VERTICAL_ALIGNMENT_FOR_HTML[$vAlign] ?? '';
    }

    /**
     * Map HAlign.
     *
     * @param string $hAlign Horizontal alignment
<<<<<<< HEAD
     */
    private function mapHAlign(string $hAlign): string
=======
     *
     * @return string
     */
    private function mapHAlign($hAlign)
>>>>>>> main
    {
        return Alignment::HORIZONTAL_ALIGNMENT_FOR_HTML[$hAlign] ?? '';
    }

<<<<<<< HEAD
    const BORDER_NONE = 'none';
    const BORDER_ARR = [
        Border::BORDER_NONE => self::BORDER_NONE,
=======
    const BORDER_ARR = [
        Border::BORDER_NONE => 'none',
>>>>>>> main
        Border::BORDER_DASHDOT => '1px dashed',
        Border::BORDER_DASHDOTDOT => '1px dotted',
        Border::BORDER_DASHED => '1px dashed',
        Border::BORDER_DOTTED => '1px dotted',
        Border::BORDER_DOUBLE => '3px double',
        Border::BORDER_HAIR => '1px solid',
        Border::BORDER_MEDIUM => '2px solid',
        Border::BORDER_MEDIUMDASHDOT => '2px dashed',
        Border::BORDER_MEDIUMDASHDOTDOT => '2px dotted',
        Border::BORDER_SLANTDASHDOT => '2px dashed',
        Border::BORDER_THICK => '3px solid',
    ];

    /**
     * Map border style.
     *
     * @param int|string $borderStyle Sheet index
<<<<<<< HEAD
     */
    private function mapBorderStyle($borderStyle): string
    {
        return self::BORDER_ARR[$borderStyle] ?? '1px solid';
=======
     *
     * @return string
     */
    private function mapBorderStyle($borderStyle)
    {
        return array_key_exists($borderStyle, self::BORDER_ARR) ? self::BORDER_ARR[$borderStyle] : '1px solid';
>>>>>>> main
    }

    /**
     * Get sheet index.
     */
    public function getSheetIndex(): ?int
    {
        return $this->sheetIndex;
    }

    /**
     * Set sheet index.
     *
     * @param int $sheetIndex Sheet index
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setSheetIndex(int $sheetIndex): static
=======
    public function setSheetIndex($sheetIndex)
>>>>>>> main
    {
        $this->sheetIndex = $sheetIndex;

        return $this;
    }

    /**
     * Get sheet index.
<<<<<<< HEAD
     */
    public function getGenerateSheetNavigationBlock(): bool
=======
     *
     * @return bool
     */
    public function getGenerateSheetNavigationBlock()
>>>>>>> main
    {
        return $this->generateSheetNavigationBlock;
    }

    /**
     * Set sheet index.
     *
     * @param bool $generateSheetNavigationBlock Flag indicating whether the sheet navigation block should be generated or not
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setGenerateSheetNavigationBlock(bool $generateSheetNavigationBlock): static
=======
    public function setGenerateSheetNavigationBlock($generateSheetNavigationBlock)
>>>>>>> main
    {
        $this->generateSheetNavigationBlock = (bool) $generateSheetNavigationBlock;

        return $this;
    }

    /**
     * Write all sheets (resets sheetIndex to NULL).
     *
     * @return $this
     */
<<<<<<< HEAD
    public function writeAllSheets(): static
=======
    public function writeAllSheets()
>>>>>>> main
    {
        $this->sheetIndex = null;

        return $this;
    }

<<<<<<< HEAD
    private function generateMeta(?string $val, string $desc): string
    {
        return ($val || $val === '0')
            ? ('      <meta name="' . $desc . '" content="' . htmlspecialchars($val, Settings::htmlEntityFlags()) . '" />' . $this->lineEnding)
            : '';
    }

    /** @deprecated 5.4.0 No replacement. */
=======
    private static function generateMeta(?string $val, string $desc): string
    {
        return ($val || $val === '0')
            ? ('      <meta name="' . $desc . '" content="' . htmlspecialchars($val, Settings::htmlEntityFlags()) . '" />' . PHP_EOL)
            : '';
    }

>>>>>>> main
    public const BODY_LINE = '  <body>' . PHP_EOL;

    private const CUSTOM_TO_META = [
        Properties::PROPERTY_TYPE_BOOLEAN => 'bool',
        Properties::PROPERTY_TYPE_DATE => 'date',
        Properties::PROPERTY_TYPE_FLOAT => 'float',
        Properties::PROPERTY_TYPE_INTEGER => 'int',
        Properties::PROPERTY_TYPE_STRING => 'string',
    ];

    /**
     * Generate HTML header.
     *
     * @param bool $includeStyles Include styles?
<<<<<<< HEAD
     */
    public function generateHTMLHeader(bool $includeStyles = false): string
    {
        // Construct HTML
        $properties = $this->spreadsheet->getProperties();
        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . $this->lineEnding;
        $rtl = ($this->rtlSheets && !$this->ltrSheets) ? " dir='rtl'" : '';
        $html .= '<html xmlns="http://www.w3.org/1999/xhtml"' . $rtl . '>' . $this->lineEnding;
        $html .= '  <head>' . $this->lineEnding;
        $html .= '      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . $this->lineEnding;
        $html .= '      <meta name="generator" content="PhpSpreadsheet, https://github.com/PHPOffice/PhpSpreadsheet" />' . $this->lineEnding;
        $title = $properties->getTitle();
        if ($title === '') {
            $title = $this->spreadsheet->getActiveSheet()->getTitle();
        }
        $html .= '      <title>' . htmlspecialchars($title, Settings::htmlEntityFlags()) . '</title>' . $this->lineEnding;
        $html .= $this->generateMeta($properties->getCreator(), 'author');
        $html .= $this->generateMeta($properties->getTitle(), 'title');
        $html .= $this->generateMeta($properties->getDescription(), 'description');
        $html .= $this->generateMeta($properties->getSubject(), 'subject');
        $html .= $this->generateMeta($properties->getKeywords(), 'keywords');
        $html .= $this->generateMeta($properties->getCategory(), 'category');
        $html .= $this->generateMeta($properties->getCompany(), 'company');
        $html .= $this->generateMeta($properties->getManager(), 'manager');
        $html .= $this->generateMeta($properties->getLastModifiedBy(), 'lastModifiedBy');
        $html .= $this->generateMeta($properties->getViewport(), 'viewport');
        $date = Date::dateTimeFromTimestamp((string) $properties->getCreated());
        $date->setTimeZone(Date::getDefaultOrLocalTimeZone());
        $html .= $this->generateMeta($date->format(DATE_W3C), 'created');
        $date = Date::dateTimeFromTimestamp((string) $properties->getModified());
        $date->setTimeZone(Date::getDefaultOrLocalTimeZone());
        $html .= $this->generateMeta($date->format(DATE_W3C), 'modified');
=======
     *
     * @return string
     */
    public function generateHTMLHeader($includeStyles = false)
    {
        // Construct HTML
        $properties = $this->spreadsheet->getProperties();
        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . PHP_EOL;
        $html .= '<html xmlns="http://www.w3.org/1999/xhtml">' . PHP_EOL;
        $html .= '  <head>' . PHP_EOL;
        $html .= '      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . PHP_EOL;
        $html .= '      <meta name="generator" content="PhpSpreadsheet, https://github.com/PHPOffice/PhpSpreadsheet" />' . PHP_EOL;
        $html .= '      <title>' . htmlspecialchars($properties->getTitle(), Settings::htmlEntityFlags()) . '</title>' . PHP_EOL;
        $html .= self::generateMeta($properties->getCreator(), 'author');
        $html .= self::generateMeta($properties->getTitle(), 'title');
        $html .= self::generateMeta($properties->getDescription(), 'description');
        $html .= self::generateMeta($properties->getSubject(), 'subject');
        $html .= self::generateMeta($properties->getKeywords(), 'keywords');
        $html .= self::generateMeta($properties->getCategory(), 'category');
        $html .= self::generateMeta($properties->getCompany(), 'company');
        $html .= self::generateMeta($properties->getManager(), 'manager');
        $html .= self::generateMeta($properties->getLastModifiedBy(), 'lastModifiedBy');
        $date = Date::dateTimeFromTimestamp((string) $properties->getCreated());
        $date->setTimeZone(Date::getDefaultOrLocalTimeZone());
        $html .= self::generateMeta($date->format(DATE_W3C), 'created');
        $date = Date::dateTimeFromTimestamp((string) $properties->getModified());
        $date->setTimeZone(Date::getDefaultOrLocalTimeZone());
        $html .= self::generateMeta($date->format(DATE_W3C), 'modified');
>>>>>>> main

        $customProperties = $properties->getCustomProperties();
        foreach ($customProperties as $customProperty) {
            $propertyValue = $properties->getCustomPropertyValue($customProperty);
            $propertyType = $properties->getCustomPropertyType($customProperty);
            $propertyQualifier = self::CUSTOM_TO_META[$propertyType] ?? null;
            if ($propertyQualifier !== null) {
                if ($propertyType === Properties::PROPERTY_TYPE_BOOLEAN) {
                    $propertyValue = $propertyValue ? '1' : '0';
                } elseif ($propertyType === Properties::PROPERTY_TYPE_DATE) {
                    $date = Date::dateTimeFromTimestamp((string) $propertyValue);
                    $date->setTimeZone(Date::getDefaultOrLocalTimeZone());
                    $propertyValue = $date->format(DATE_W3C);
                } else {
                    $propertyValue = (string) $propertyValue;
                }
<<<<<<< HEAD
                $html .= $this->generateMeta($propertyValue, htmlspecialchars("custom.$propertyQualifier.$customProperty"));
=======
                $html .= self::generateMeta($propertyValue, htmlspecialchars("custom.$propertyQualifier.$customProperty"));
>>>>>>> main
            }
        }

        if (!empty($properties->getHyperlinkBase())) {
<<<<<<< HEAD
            $html .= '      <base href="' . htmlspecialchars($properties->getHyperlinkBase()) . '" />' . $this->lineEnding;
=======
            $html .= '      <base href="' . htmlspecialchars($properties->getHyperlinkBase()) . '" />' . PHP_EOL;
>>>>>>> main
        }

        $html .= $includeStyles ? $this->generateStyles(true) : $this->generatePageDeclarations(true);

<<<<<<< HEAD
        $html .= '  </head>' . $this->lineEnding;
        $html .= '' . $this->lineEnding;
        $html .= '  <body>' . $this->lineEnding;
=======
        $html .= '  </head>' . PHP_EOL;
        $html .= '' . PHP_EOL;
        $html .= self::BODY_LINE;
>>>>>>> main

        return $html;
    }

<<<<<<< HEAD
    /** @return Worksheet[] */
    private function generateSheetPrep(): array
    {
=======
    private function generateSheetPrep(): array
    {
        // Ensure that Spans have been calculated?
        $this->calculateSpans();

>>>>>>> main
        // Fetch sheets
        if ($this->sheetIndex === null) {
            $sheets = $this->spreadsheet->getAllSheets();
        } else {
            $sheets = [$this->spreadsheet->getSheet($this->sheetIndex)];
        }

        return $sheets;
    }

<<<<<<< HEAD
    /** @return array{int, int, int} */
=======
>>>>>>> main
    private function generateSheetStarts(Worksheet $sheet, int $rowMin): array
    {
        // calculate start of <tbody>, <thead>
        $tbodyStart = $rowMin;
        $theadStart = $theadEnd = 0; // default: no <thead>    no </thead>
        if ($sheet->getPageSetup()->isRowsToRepeatAtTopSet()) {
            $rowsToRepeatAtTop = $sheet->getPageSetup()->getRowsToRepeatAtTop();

            // we can only support repeating rows that start at top row
            if ($rowsToRepeatAtTop[0] == 1) {
                $theadStart = $rowsToRepeatAtTop[0];
                $theadEnd = $rowsToRepeatAtTop[1];
                $tbodyStart = $rowsToRepeatAtTop[1] + 1;
            }
        }

        return [$theadStart, $theadEnd, $tbodyStart];
    }

<<<<<<< HEAD
    /** @return array{string, string, string} */
    private function generateSheetTags(int $row, int $theadStart, int $theadEnd, int $tbodyStart): array
    {
        // <thead> ?
        $startTag = ($row == $theadStart) ? ('        <thead>' . $this->lineEnding) : '';
        if (!$startTag) {
            $startTag = ($row == $tbodyStart) ? ('        <tbody>' . $this->lineEnding) : '';
        }
        $endTag = ($row == $theadEnd) ? ('        </thead>' . $this->lineEnding) : '';
=======
    private function generateSheetTags(int $row, int $theadStart, int $theadEnd, int $tbodyStart): array
    {
        // <thead> ?
        $startTag = ($row == $theadStart) ? ('        <thead>' . PHP_EOL) : '';
        if (!$startTag) {
            $startTag = ($row == $tbodyStart) ? ('        <tbody>' . PHP_EOL) : '';
        }
        $endTag = ($row == $theadEnd) ? ('        </thead>' . PHP_EOL) : '';
>>>>>>> main
        $cellType = ($row >= $tbodyStart) ? 'td' : 'th';

        return [$cellType, $startTag, $endTag];
    }

<<<<<<< HEAD
    private int $printAreaLowRow = -1;

    private int $printAreaHighRow = -1;

    private int $printAreaLowCol = -1;

    private int $printAreaHighCol = -1;

    /**
     * Generate sheet data.
     */
    public function generateSheetData(): string
    {
        // Ensure that Spans have been calculated?
        $this->calculateSpans();
=======
    /**
     * Generate sheet data.
     *
     * @return string
     */
    public function generateSheetData()
    {
>>>>>>> main
        $sheets = $this->generateSheetPrep();

        // Construct HTML
        $html = '';

        // Loop all sheets
        $sheetId = 0;
<<<<<<< HEAD

        $activeSheet = $this->spreadsheet->getActiveSheetIndex();

        foreach ($sheets as $sheet) {
            $this->printAreaLowRow = -1;
            $this->printAreaHighRow = -1;
            $this->printAreaLowCol = -1;
            $this->printAreaHighCol = -1;
            $printArea = $sheet->getPageSetup()->getPrintArea();
            if (Preg::isMatch('/^([a-z]+)([0-9]+):([a-z]+)([0-9]+)$/i', $printArea, $matches)) {
                $this->printAreaLowCol = Coordinate::columnIndexFromString($matches[1]);
                $this->printAreaHighCol = Coordinate::columnIndexFromString($matches[3]);
                $this->printAreaLowRow = (int) $matches[2];
                $this->printAreaHighRow = (int) $matches[4];
            }
            // save active cells
            $selectedCells = $sheet->getSelectedCells();
            // Write table header
            $html .= $this->generateTableHeader($sheet);
            $this->sheetCharts = [];
            $this->sheetDrawings = [];
            $condStylesCollection = $sheet->getConditionalStylesCollection();
            foreach ($condStylesCollection as $condStyles) {
                foreach ($condStyles as $key => $cs) {
                    if ($cs->getConditionType() === Conditional::CONDITION_COLORSCALE) {
                        $cs->getColorScale()?->setScaleArray();
                    }
                }
            }
            // Get worksheet dimension
            [$min, $max] = explode(':', $sheet->calculateWorksheetDataDimension());
            [$minCol, $minRow, $minColString] = Coordinate::indexesFromString($min);
            [$maxCol, $maxRow] = Coordinate::indexesFromString($max);
            $this->extendRowsAndColumns($sheet, $maxCol, $maxRow);
            $this->extendRowsAndColumnsForMerge($sheet, $maxCol, $maxRow);

            [$theadStart, $theadEnd, $tbodyStart] = $this->generateSheetStarts($sheet, $minRow);
=======
        foreach ($sheets as $sheet) {
            // Write table header
            $html .= $this->generateTableHeader($sheet);

            // Get worksheet dimension
            [$min, $max] = explode(':', $sheet->calculateWorksheetDataDimension());
            [$minCol, $minRow] = Coordinate::indexesFromString($min);
            [$maxCol, $maxRow] = Coordinate::indexesFromString($max);

            [$theadStart, $theadEnd, $tbodyStart] = $this->generateSheetStarts($sheet, $minRow);

>>>>>>> main
            // Loop through cells
            $row = $minRow - 1;
            while ($row++ < $maxRow) {
                [$cellType, $startTag, $endTag] = $this->generateSheetTags($row, $theadStart, $theadEnd, $tbodyStart);
<<<<<<< HEAD
                $html .= StringHelper::convertToString($startTag);

                // Write row if there are HTML table cells in it
                if ($this->shouldGenerateRow($sheet, $row)) {
=======
                $html .= $startTag;

                // Write row if there are HTML table cells in it
                if (!isset($this->isSpannedRow[$sheet->getParent()->getIndex($sheet)][$row])) {
>>>>>>> main
                    // Start a new rowData
                    $rowData = [];
                    // Loop through columns
                    $column = $minCol;
<<<<<<< HEAD
                    $colStr = $minColString;
                    while ($column <= $maxCol) {
                        // Cell exists?
                        $cellAddress = Coordinate::stringFromColumnIndex($column) . $row;
                        if ($this->shouldGenerateColumn($sheet, $colStr)) {
                            $rowData[$column] = ($sheet->getCellCollection()->has($cellAddress)) ? $cellAddress : '';
                        }
                        ++$column;
                        /** @var string $colStr */
                        StringHelper::stringIncrement($colStr);
=======
                    while ($column <= $maxCol) {
                        // Cell exists?
                        $cellAddress = Coordinate::stringFromColumnIndex($column) . $row;
                        $rowData[$column++] = ($sheet->getCellCollection()->has($cellAddress)) ? $cellAddress : '';
>>>>>>> main
                    }
                    $html .= $this->generateRow($sheet, $rowData, $row - 1, $cellType);
                }

<<<<<<< HEAD
                $html .= StringHelper::convertToString($endTag);
            }
            // Write table footer
            $html .= $this->generateTableFooter();
            // Writing PDF?
            if ($this instanceof Pdf\Tcpdf && $this->useInlineCss) {
=======
                $html .= $endTag;
            }
            --$row;
            $html .= $this->extendRowsForChartsAndImages($sheet, $row);

            // Write table footer
            $html .= $this->generateTableFooter();
            // Writing PDF?
            if ($this->isPdf && $this->useInlineCss) {
>>>>>>> main
                if ($this->sheetIndex === null && $sheetId + 1 < $this->spreadsheet->getSheetCount()) {
                    $html .= '<div style="page-break-before:always" ></div>';
                }
            }

            // Next sheet
            ++$sheetId;
<<<<<<< HEAD
            $sheet->setSelectedCells($selectedCells);
        }
        $this->spreadsheet->setActiveSheetIndex($activeSheet);
=======
        }
>>>>>>> main

        return $html;
    }

    /**
     * Generate sheet tabs.
<<<<<<< HEAD
     */
    public function generateNavigation(): string
=======
     *
     * @return string
     */
    public function generateNavigation()
>>>>>>> main
    {
        // Fetch sheets
        $sheets = [];
        if ($this->sheetIndex === null) {
            $sheets = $this->spreadsheet->getAllSheets();
        } else {
            $sheets[] = $this->spreadsheet->getSheet($this->sheetIndex);
        }

        // Construct HTML
        $html = '';

        // Only if there are more than 1 sheets
        if (count($sheets) > 1) {
            // Loop all sheets
            $sheetId = 0;

<<<<<<< HEAD
            $html .= '<ul class="navigation">' . $this->lineEnding;

            foreach ($sheets as $sheet) {
                $html .= '  <li class="sheet' . $sheetId . '"><a href="#sheet' . $sheetId . '">' . htmlspecialchars($sheet->getTitle()) . '</a></li>' . $this->lineEnding;
                ++$sheetId;
            }

            $html .= '</ul>' . $this->lineEnding;
=======
            $html .= '<ul class="navigation">' . PHP_EOL;

            foreach ($sheets as $sheet) {
                $html .= '  <li class="sheet' . $sheetId . '"><a href="#sheet' . $sheetId . '">' . htmlspecialchars($sheet->getTitle()) . '</a></li>' . PHP_EOL;
                ++$sheetId;
            }

            $html .= '</ul>' . PHP_EOL;
>>>>>>> main
        }

        return $html;
    }

<<<<<<< HEAD
    private function extendRowsAndColumns(Worksheet $worksheet, int &$colMax, int &$rowMax): void
    {
        if ($this->includeCharts) {
            foreach ($worksheet->getChartCollection() as $chart) {
                $chartCoordinates = $chart->getTopLeftPosition();
                $this->sheetCharts[$chartCoordinates['cell']] = $chart;
                $chartTL = Coordinate::indexesFromString($chartCoordinates['cell']);
                if ($chartTL[1] > $rowMax) {
                    $rowMax = $chartTL[1];
                }
                if ($chartTL[0] > $colMax) {
                    $colMax = $chartTL[0];
                }
            }
        }
=======
    /**
     * Extend Row if chart is placed after nominal end of row.
     * This code should be exercised by sample:
     * Chart/32_Chart_read_write_PDF.php.
     *
     * @param int $row Row to check for charts
     *
     * @return array
     */
    private function extendRowsForCharts(Worksheet $worksheet, int $row)
    {
        $rowMax = $row;
        $colMax = 'A';
        $anyfound = false;
        if ($this->includeCharts) {
            foreach ($worksheet->getChartCollection() as $chart) {
                if ($chart instanceof Chart) {
                    $anyfound = true;
                    $chartCoordinates = $chart->getTopLeftPosition();
                    $chartTL = Coordinate::coordinateFromString($chartCoordinates['cell']);
                    $chartCol = Coordinate::columnIndexFromString($chartTL[0]);
                    if ($chartTL[1] > $rowMax) {
                        $rowMax = $chartTL[1];
                        if ($chartCol > Coordinate::columnIndexFromString($colMax)) {
                            $colMax = $chartTL[0];
                        }
                    }
                }
            }
        }

        return [$rowMax, $colMax, $anyfound];
    }

    private function extendRowsForChartsAndImages(Worksheet $worksheet, int $row): string
    {
        [$rowMax, $colMax, $anyfound] = $this->extendRowsForCharts($worksheet, $row);

>>>>>>> main
        foreach ($worksheet->getDrawingCollection() as $drawing) {
            if ($drawing instanceof Drawing && $drawing->getPath() === '') {
                continue;
            }
<<<<<<< HEAD
            $imageTL = Coordinate::indexesFromString($drawing->getCoordinates());
            $this->sheetDrawings[$drawing->getCoordinates()] = $drawing;
            if ($imageTL[1] > $rowMax) {
                $rowMax = $imageTL[1];
            }
            if ($imageTL[0] > $colMax) {
                $colMax = $imageTL[0];
            }
        }
=======
            $anyfound = true;
            $imageTL = Coordinate::coordinateFromString($drawing->getCoordinates());
            $imageCol = Coordinate::columnIndexFromString($imageTL[0]);
            if ($imageTL[1] > $rowMax) {
                $rowMax = $imageTL[1];
                if ($imageCol > Coordinate::columnIndexFromString($colMax)) {
                    $colMax = $imageTL[0];
                }
            }
        }

        // Don't extend rows if not needed
        if ($row === $rowMax || !$anyfound) {
            return '';
        }

        $html = '';
        ++$colMax;
        ++$row;
        while ($row <= $rowMax) {
            $html .= '<tr>';
            for ($col = 'A'; $col != $colMax; ++$col) {
                $htmlx = $this->writeImageInCell($worksheet, $col . $row);
                $htmlx .= $this->includeCharts ? $this->writeChartInCell($worksheet, $col . $row) : '';
                if ($htmlx) {
                    $html .= "<td class='style0' style='position: relative;'>$htmlx</td>";
                } else {
                    $html .= "<td class='style0'></td>";
                }
            }
            ++$row;
            $html .= '</tr>' . PHP_EOL;
        }

        return $html;
>>>>>>> main
    }

    /**
     * Convert Windows file name to file protocol URL.
     *
     * @param string $filename file name on local system
<<<<<<< HEAD
     */
    public static function winFileToUrl(string $filename, bool $mpdf = false): string
=======
     *
     * @return string
     */
    public static function winFileToUrl($filename, bool $mpdf = false)
>>>>>>> main
    {
        // Windows filename
        if (substr($filename, 1, 2) === ':\\') {
            $protocol = $mpdf ? '' : 'file:///';
            $filename = $protocol . str_replace('\\', '/', $filename);
        }

        return $filename;
    }

    /**
     * Generate image tag in cell.
     *
<<<<<<< HEAD
     * @param string $coordinates Cell coordinates
     */
    private function writeImageInCell(string $coordinates): string
=======
     * @param Worksheet $worksheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     * @param string $coordinates Cell coordinates
     *
     * @return string
     */
    private function writeImageInCell(Worksheet $worksheet, $coordinates)
>>>>>>> main
    {
        // Construct HTML
        $html = '';

        // Write images
<<<<<<< HEAD
        $drawing = $this->sheetDrawings[$coordinates] ?? null;
        if ($drawing !== null) {
            $opacity = '';
            $opacityValue = $drawing->getOpacity();
            if ($opacityValue !== null) {
                $opacityValue = $opacityValue / 100000;
                if ($opacityValue >= 0.0 && $opacityValue <= 1.0) {
                    $opacity = "opacity:$opacityValue; ";
                }
=======
        foreach ($worksheet->getDrawingCollection() as $drawing) {
            if ($drawing->getCoordinates() != $coordinates) {
                continue;
>>>>>>> main
            }
            $filedesc = $drawing->getDescription();
            $filedesc = $filedesc ? htmlspecialchars($filedesc, ENT_QUOTES) : 'Embedded image';
            if ($drawing instanceof Drawing && $drawing->getPath() !== '') {
                $filename = $drawing->getPath();

                // Strip off eventual '.'
<<<<<<< HEAD
                $filename = Preg::replace('/^[.]/', '', $filename);
=======
                $filename = (string) preg_replace('/^[.]/', '', $filename);
>>>>>>> main

                // Prepend images root
                $filename = $this->getImagesRoot() . $filename;

                // Strip off eventual '.' if followed by non-/
<<<<<<< HEAD
                $filename = Preg::replace('@^[.]([^/])@', '$1', $filename);
=======
                $filename = (string) preg_replace('@^[.]([^/])@', '$1', $filename);
>>>>>>> main

                // Convert UTF8 data to PCDATA
                $filename = htmlspecialchars($filename, Settings::htmlEntityFlags());

<<<<<<< HEAD
                $html .= $this->lineEnding;
                $imageData = self::winFileToUrl($filename, $this instanceof Pdf\Mpdf);

                if ($this->embedImages || str_starts_with($imageData, 'zip://')) {
=======
                $html .= PHP_EOL;
                $imageData = self::winFileToUrl($filename, $this->isMPdf);

                if ($this->embedImages || substr($imageData, 0, 6) === 'zip://') {
>>>>>>> main
                    $imageData = 'data:,';
                    $picture = @file_get_contents($filename);
                    if ($picture !== false) {
                        $mimeContentType = (string) @mime_content_type($filename);
<<<<<<< HEAD
                        if (str_starts_with($mimeContentType, 'image/')) {
=======
                        if (substr($mimeContentType, 0, 6) === 'image/') {
>>>>>>> main
                            // base64 encode the binary data
                            $base64 = base64_encode($picture);
                            $imageData = 'data:' . $mimeContentType . ';base64,' . $base64;
                        }
                    }
                }

<<<<<<< HEAD
                $html .= '<img style="' . $opacity . 'position: absolute; z-index: 1; left: '
                    . $drawing->getOffsetX() . 'px; top: ' . $drawing->getOffsetY() . 'px; width: '
                    . $drawing->getWidth() . 'px; height: ' . $drawing->getHeight() . 'px;" src="'
                    . $imageData . '" alt="' . $filedesc . '" />';
=======
                $html .= '<img style="position: absolute; z-index: 1; left: ' .
                    $drawing->getOffsetX() . 'px; top: ' . $drawing->getOffsetY() . 'px; width: ' .
                    $drawing->getWidth() . 'px; height: ' . $drawing->getHeight() . 'px;" src="' .
                    $imageData . '" alt="' . $filedesc . '" />';
>>>>>>> main
            } elseif ($drawing instanceof MemoryDrawing) {
                $imageResource = $drawing->getImageResource();
                if ($imageResource) {
                    ob_start(); //  Let's start output buffering.
                    imagepng($imageResource); //  This will normally output the image, but because of ob_start(), it won't.
                    $contents = (string) ob_get_contents(); //  Instead, output above is saved to $contents
                    ob_end_clean(); //  End the output buffer.

                    $dataUri = 'data:image/png;base64,' . base64_encode($contents);

                    //  Because of the nature of tables, width is more important than height.
<<<<<<< HEAD
                    //  max-width: 100% ensures that image doesn't overflow containing cell
                    //    However, PR #3535 broke test
                    //    25_In_memory_image, apparently because
                    //    of the use of max-with. In addition,
                    //    non-memory-drawings don't use max-width.
                    //    Its use here is suspect and is being eliminated.
                    //  width: X sets width of supplied image.
                    //  As a result, images bigger than cell will be contained and images smaller will not get stretched
                    $html .= '<img alt="' . $filedesc . '" src="' . $dataUri . '" style="' . $opacity . 'width:' . $drawing->getWidth() . 'px;left: '
                        . $drawing->getOffsetX() . 'px; top: ' . $drawing->getOffsetY() . 'px;position: absolute; z-index: 1;" />';
=======
                    //  max-width: 100% ensures that image doesnt overflow containing cell
                    //  width: X sets width of supplied image.
                    //  As a result, images bigger than cell will be contained and images smaller will not get stretched
                    $html .= '<img alt="' . $filedesc . '" src="' . $dataUri . '" style="max-width:100%;width:' . $drawing->getWidth() . 'px;left: ' .
                    $drawing->getOffsetX() . 'px; top: ' . $drawing->getOffsetY() . 'px;position: absolute; z-index: 1;" />';
>>>>>>> main
                }
            }
        }

        return $html;
    }

    /**
     * Generate chart tag in cell.
     * This code should be exercised by sample:
     * Chart/32_Chart_read_write_PDF.php.
     */
    private function writeChartInCell(Worksheet $worksheet, string $coordinates): string
    {
        // Construct HTML
        $html = '';

        // Write charts
<<<<<<< HEAD
        $chart = $this->sheetCharts[$coordinates] ?? null;
        if ($chart !== null) {
            $chartCoordinates = $chart->getTopLeftPosition();
            $chartFileName = File::sysGetTempDir() . '/' . uniqid('', true) . '.png';
            $renderedWidth = $chart->getRenderedWidth();
            $renderedHeight = $chart->getRenderedHeight();
            if ($renderedWidth === null || $renderedHeight === null) {
                $this->adjustRendererPositions($chart, $worksheet);
            }
            $title = $chart->getTitle();
            $caption = null;
            $filedesc = '';
            if ($title !== null) {
                $calculatedTitle = $title->getCalculatedTitle($worksheet->getParent());
                if ($calculatedTitle !== null) {
                    $caption = $title->getCaption();
                    $title->setCaption($calculatedTitle);
                }
                $filedesc = $title->getCaptionText($worksheet->getParent());
            }
            $renderSuccessful = $chart->render($chartFileName);
            $chart->setRenderedWidth($renderedWidth);
            $chart->setRenderedHeight($renderedHeight);
            if (isset($title, $caption)) {
                $title->setCaption($caption);
            }
            if (!$renderSuccessful) {
                return '';
            }

            $html .= $this->lineEnding;
            $imageDetails = getimagesize($chartFileName) ?: ['', '', 'mime' => ''];

            $filedesc = $filedesc ? htmlspecialchars($filedesc, ENT_QUOTES) : 'Embedded chart';
            $picture = file_get_contents($chartFileName);
            unlink($chartFileName);
            if ($picture !== false) {
                $base64 = base64_encode($picture);
                $imageData = 'data:' . $imageDetails['mime'] . ';base64,' . $base64;

                $html .= '<img style="position: absolute; z-index: 1; left: ' . $chartCoordinates['xOffset'] . 'px; top: ' . $chartCoordinates['yOffset'] . 'px; width: ' . $imageDetails[0] . 'px; height: ' . $imageDetails[1] . 'px;" src="' . $imageData . '" alt="' . $filedesc . '" />' . $this->lineEnding;
=======
        foreach ($worksheet->getChartCollection() as $chart) {
            if ($chart instanceof Chart) {
                $chartCoordinates = $chart->getTopLeftPosition();
                if ($chartCoordinates['cell'] == $coordinates) {
                    $chartFileName = File::sysGetTempDir() . '/' . uniqid('', true) . '.png';
                    if (!$chart->render($chartFileName)) {
                        return '';
                    }

                    $html .= PHP_EOL;
                    $imageDetails = getimagesize($chartFileName) ?: [];
                    $filedesc = $chart->getTitle();
                    $filedesc = $filedesc ? $filedesc->getCaptionText() : '';
                    $filedesc = $filedesc ? htmlspecialchars($filedesc, ENT_QUOTES) : 'Embedded chart';
                    $picture = file_get_contents($chartFileName);
                    if ($picture !== false) {
                        $base64 = base64_encode($picture);
                        $imageData = 'data:' . $imageDetails['mime'] . ';base64,' . $base64;

                        $html .= '<img style="position: absolute; z-index: 1; left: ' . $chartCoordinates['xOffset'] . 'px; top: ' . $chartCoordinates['yOffset'] . 'px; width: ' . $imageDetails[0] . 'px; height: ' . $imageDetails[1] . 'px;" src="' . $imageData . '" alt="' . $filedesc . '" />' . PHP_EOL;
                    }
                    unlink($chartFileName);
                }
>>>>>>> main
            }
        }

        // Return
        return $html;
    }

<<<<<<< HEAD
    private function adjustRendererPositions(Chart $chart, Worksheet $sheet): void
    {
        $topLeft = $chart->getTopLeftPosition();
        $bottomRight = $chart->getBottomRightPosition();
        $tlCell = $topLeft['cell'];
        /** @var string */
        $brCell = $bottomRight['cell'];
        if ($tlCell !== '' && $brCell !== '') {
            $tlCoordinate = Coordinate::indexesFromString($tlCell);
            $brCoordinate = Coordinate::indexesFromString($brCell);
            $totalHeight = 0.0;
            $totalWidth = 0.0;
            $defaultRowHeight = $sheet->getDefaultRowDimension()->getRowHeight();
            $defaultRowHeight = SharedDrawing::pointsToPixels(($defaultRowHeight >= 0) ? $defaultRowHeight : SharedFont::getDefaultRowHeightByFont($this->defaultFont));
            if ($tlCoordinate[1] <= $brCoordinate[1] && $tlCoordinate[0] <= $brCoordinate[0]) {
                for ($row = $tlCoordinate[1]; $row <= $brCoordinate[1]; ++$row) {
                    $height = $sheet->getRowDimension($row)->getRowHeight('pt');
                    $totalHeight += ($height >= 0) ? $height : $defaultRowHeight;
                }
                $rightEdge = $brCoordinate[2];
                StringHelper::stringIncrement($rightEdge);
                for ($column = $tlCoordinate[2]; $column !== $rightEdge;) {
                    $width = $sheet->getColumnDimension($column)->getWidth();
                    $width = ($width < 0) ? self::DEFAULT_CELL_WIDTH_PIXELS : SharedDrawing::cellDimensionToPixels($sheet->getColumnDimension($column)->getWidth(), $this->defaultFont);
                    $totalWidth += $width;
                    StringHelper::stringIncrement($column);
                }
                $chart->setRenderedWidth($totalWidth);
                $chart->setRenderedHeight($totalHeight);
            }
        }
    }

=======
>>>>>>> main
    /**
     * Generate CSS styles.
     *
     * @param bool $generateSurroundingHTML Generate surrounding HTML tags? (&lt;style&gt; and &lt;/style&gt;)
<<<<<<< HEAD
     */
    public function generateStyles(bool $generateSurroundingHTML = true): string
=======
     *
     * @return string
     */
    public function generateStyles($generateSurroundingHTML = true)
>>>>>>> main
    {
        // Build CSS
        $css = $this->buildCSS($generateSurroundingHTML);

        // Construct HTML
        $html = '';

        // Start styles
        if ($generateSurroundingHTML) {
<<<<<<< HEAD
            $html .= '    <style type="text/css">' . $this->lineEnding;
            $html .= (array_key_exists('html', $css)) ? ('      html { ' . $this->assembleCSS($css['html']) . ' }' . $this->lineEnding) : '';
=======
            $html .= '    <style type="text/css">' . PHP_EOL;
            $html .= (array_key_exists('html', $css)) ? ('      html { ' . $this->assembleCSS($css['html']) . ' }' . PHP_EOL) : '';
>>>>>>> main
        }

        // Write all other styles
        foreach ($css as $styleName => $styleDefinition) {
            if ($styleName != 'html') {
<<<<<<< HEAD
                $html .= '      ' . $styleName . ' { ' . $this->assembleCSS($styleDefinition) . ' }' . $this->lineEnding;
=======
                $html .= '      ' . $styleName . ' { ' . $this->assembleCSS($styleDefinition) . ' }' . PHP_EOL;
>>>>>>> main
            }
        }
        $html .= $this->generatePageDeclarations(false);

        // End styles
        if ($generateSurroundingHTML) {
<<<<<<< HEAD
            $html .= '    </style>' . $this->lineEnding;
=======
            $html .= '    </style>' . PHP_EOL;
>>>>>>> main
        }

        // Return
        return $html;
    }

<<<<<<< HEAD
    /** @param string[][] $css */
=======
>>>>>>> main
    private function buildCssRowHeights(Worksheet $sheet, array &$css, int $sheetIndex): void
    {
        // Calculate row heights
        foreach ($sheet->getRowDimensions() as $rowDimension) {
            $row = $rowDimension->getRowIndex() - 1;

            // table.sheetN tr.rowYYYYYY { }
            $css['table.sheet' . $sheetIndex . ' tr.row' . $row] = [];

            if ($rowDimension->getRowHeight() != -1) {
                $pt_height = $rowDimension->getRowHeight();
                $css['table.sheet' . $sheetIndex . ' tr.row' . $row]['height'] = $pt_height . 'pt';
            }
            if ($rowDimension->getVisible() === false) {
                $css['table.sheet' . $sheetIndex . ' tr.row' . $row]['display'] = 'none';
                $css['table.sheet' . $sheetIndex . ' tr.row' . $row]['visibility'] = 'hidden';
            }
        }
    }

<<<<<<< HEAD
    /** @param string[][] $css */
=======
>>>>>>> main
    private function buildCssPerSheet(Worksheet $sheet, array &$css): void
    {
        // Calculate hash code
        $sheetIndex = $sheet->getParentOrThrow()->getIndex($sheet);
        $setup = $sheet->getPageSetup();
        if ($setup->getFitToPage() && $setup->getFitToHeight() === 1) {
            $css["table.sheet$sheetIndex"]['page-break-inside'] = 'avoid';
            $css["table.sheet$sheetIndex"]['break-inside'] = 'avoid';
        }
<<<<<<< HEAD
        $picture = $sheet->getBackgroundImage();
        if ($picture !== '') {
            $base64 = base64_encode($picture);
            $css["table.sheet$sheetIndex"]['background-image'] = 'url(data:' . $sheet->getBackgroundMime() . ';base64,' . $base64 . ')';
        }
=======
>>>>>>> main

        // Build styles
        // Calculate column widths
        $sheet->calculateColumnWidths();

        // col elements, initialize
        $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn()) - 1;
        $column = -1;
<<<<<<< HEAD
        $colStr = 'A';
        while ($column++ < $highestColumnIndex) {
            $this->columnWidths[$sheetIndex][$column] = self::DEFAULT_CELL_WIDTH_POINTS; // approximation
            if ($this->shouldGenerateColumn($sheet, $colStr)) {
                $css['table.sheet' . $sheetIndex . ' col.col' . $column]['width'] = self::DEFAULT_CELL_WIDTH_POINTS . 'pt';
            }
            StringHelper::stringIncrement($colStr);
=======
        while ($column++ < $highestColumnIndex) {
            $this->columnWidths[$sheetIndex][$column] = 42; // approximation
            $css['table.sheet' . $sheetIndex . ' col.col' . $column]['width'] = '42pt';
>>>>>>> main
        }

        // col elements, loop through columnDimensions and set width
        foreach ($sheet->getColumnDimensions() as $columnDimension) {
            $column = Coordinate::columnIndexFromString($columnDimension->getColumnIndex()) - 1;
            $width = SharedDrawing::cellDimensionToPixels($columnDimension->getWidth(), $this->defaultFont);
            $width = SharedDrawing::pixelsToPoints($width);
            if ($columnDimension->getVisible() === false) {
                $css['table.sheet' . $sheetIndex . ' .column' . $column]['display'] = 'none';
<<<<<<< HEAD
                // This would be better but Firefox has an 11-year-old bug.
                // https://bugzilla.mozilla.org/show_bug.cgi?id=819045
                //$css['table.sheet' . $sheetIndex . ' col.col' . $column]['visibility'] = 'collapse';
=======
>>>>>>> main
            }
            if ($width >= 0) {
                $this->columnWidths[$sheetIndex][$column] = $width;
                $css['table.sheet' . $sheetIndex . ' col.col' . $column]['width'] = $width . 'pt';
            }
        }

        // Default row height
        $rowDimension = $sheet->getDefaultRowDimension();

        // table.sheetN tr { }
        $css['table.sheet' . $sheetIndex . ' tr'] = [];

        if ($rowDimension->getRowHeight() == -1) {
            $pt_height = SharedFont::getDefaultRowHeightByFont($this->spreadsheet->getDefaultStyle()->getFont());
        } else {
            $pt_height = $rowDimension->getRowHeight();
        }
        $css['table.sheet' . $sheetIndex . ' tr']['height'] = $pt_height . 'pt';
        if ($rowDimension->getVisible() === false) {
            $css['table.sheet' . $sheetIndex . ' tr']['display'] = 'none';
            $css['table.sheet' . $sheetIndex . ' tr']['visibility'] = 'hidden';
        }

        $this->buildCssRowHeights($sheet, $css, $sheetIndex);
    }

    /**
     * Build CSS styles.
     *
     * @param bool $generateSurroundingHTML Generate surrounding HTML style? (html { })
     *
<<<<<<< HEAD
     * @return string[][]
     */
    public function buildCSS(bool $generateSurroundingHTML = true): array
=======
     * @return array
     */
    public function buildCSS($generateSurroundingHTML = true)
>>>>>>> main
    {
        // Cached?
        if ($this->cssStyles !== null) {
            return $this->cssStyles;
        }

        // Ensure that spans have been calculated
        $this->calculateSpans();

        // Construct CSS
<<<<<<< HEAD
        /** @var string[][] */
=======
>>>>>>> main
        $css = [];

        // Start styles
        if ($generateSurroundingHTML) {
            // html { }
            $css['html']['font-family'] = 'Calibri, Arial, Helvetica, sans-serif';
            $css['html']['font-size'] = '11pt';
            $css['html']['background-color'] = 'white';
        }

        // CSS for comments as found in LibreOffice
        $css['a.comment-indicator:hover + div.comment'] = [
            'background' => '#ffd',
            'position' => 'absolute',
            'display' => 'block',
            'border' => '1px solid black',
            'padding' => '0.5em',
        ];

        $css['a.comment-indicator'] = [
            'background' => 'red',
            'display' => 'inline-block',
            'border' => '1px solid black',
            'width' => '0.5em',
            'height' => '0.5em',
        ];

        $css['div.comment']['display'] = 'none';

        // table { }
        $css['table']['border-collapse'] = 'collapse';

        // .b {}
        $css['.b']['text-align'] = 'center'; // BOOL

        // .e {}
        $css['.e']['text-align'] = 'center'; // ERROR

        // .f {}
        $css['.f']['text-align'] = 'right'; // FORMULA

        // .inlineStr {}
        $css['.inlineStr']['text-align'] = 'left'; // INLINE

        // .n {}
        $css['.n']['text-align'] = 'right'; // NUMERIC

        // .s {}
        $css['.s']['text-align'] = 'left'; // STRING

<<<<<<< HEAD
        $css['.floatright']['float'] = 'right';
        $css['.floatleft']['float'] = 'left';

=======
>>>>>>> main
        // Calculate cell style hashes
        foreach ($this->spreadsheet->getCellXfCollection() as $index => $style) {
            $css['td.style' . $index . ', th.style' . $index] = $this->createCSSStyle($style);
            //$css['th.style' . $index] = $this->createCSSStyle($style);
        }

        // Fetch sheets
        $sheets = [];
        if ($this->sheetIndex === null) {
            $sheets = $this->spreadsheet->getAllSheets();
        } else {
            $sheets[] = $this->spreadsheet->getSheet($this->sheetIndex);
        }

        // Build styles per sheet
        foreach ($sheets as $sheet) {
            $this->buildCssPerSheet($sheet, $css);
        }

        // Cache
        if ($this->cssStyles === null) {
            $this->cssStyles = $css;
        }

        // Return
        return $css;
    }

    /**
     * Create CSS style.
     *
<<<<<<< HEAD
     * @return string[]
     */
    private function createCSSStyle(Style $style, bool $conditional = false): array
    {
        // Create CSS
        return array_merge(
            $conditional ? [] : $this->createCSSStyleAlignment($style->getAlignment()),
            $this->createCSSStyleBorders($style->getBorders()),
            $this->createCSSStyleFont($style->getFont(), conditional: $conditional),
=======
     * @return array
     */
    private function createCSSStyle(Style $style)
    {
        // Create CSS
        return array_merge(
            $this->createCSSStyleAlignment($style->getAlignment()),
            $this->createCSSStyleBorders($style->getBorders()),
            $this->createCSSStyleFont($style->getFont()),
>>>>>>> main
            $this->createCSSStyleFill($style->getFill())
        );
    }

    /**
     * Create CSS style.
     *
<<<<<<< HEAD
     * @return string[]
     */
    private function createCSSStyleAlignment(Alignment $alignment): array
=======
     * @return array
     */
    private function createCSSStyleAlignment(Alignment $alignment)
>>>>>>> main
    {
        // Construct CSS
        $css = [];

        // Create CSS
        $verticalAlign = $this->mapVAlign($alignment->getVertical() ?? '');
        if ($verticalAlign) {
            $css['vertical-align'] = $verticalAlign;
        }
        $textAlign = $this->mapHAlign($alignment->getHorizontal() ?? '');
        if ($textAlign) {
            $css['text-align'] = $textAlign;
            if (in_array($textAlign, ['left', 'right'])) {
<<<<<<< HEAD
                $css['padding-' . $textAlign] = (string) ($alignment->getIndent() * Alignment::INDENT_UNITS_TO_PIXELS) . 'px';
            }
        } else {
            $indent = $alignment->getIndent();
            if ($indent !== 0) {
                $css['text-indent'] = (string) ($alignment->getIndent() * Alignment::INDENT_UNITS_TO_PIXELS) . 'px';
=======
                $css['padding-' . $textAlign] = (string) ((int) $alignment->getIndent() * 9) . 'px';
>>>>>>> main
            }
        }
        $rotation = $alignment->getTextRotation();
        if ($rotation !== 0 && $rotation !== Alignment::TEXTROTATION_STACK_PHPSPREADSHEET) {
<<<<<<< HEAD
            if ($this instanceof Pdf\Mpdf) {
=======
            if ($this->isMPdf) {
>>>>>>> main
                $css['text-rotate'] = "$rotation";
            } else {
                $css['transform'] = "rotate({$rotation}deg)";
            }
        }
<<<<<<< HEAD
        $direction = $alignment->getReadOrder();
        if ($direction === Alignment::READORDER_LTR) {
            $css['direction'] = 'ltr';
        } elseif ($direction === Alignment::READORDER_RTL) {
            $css['direction'] = 'rtl';
        }
=======
>>>>>>> main

        return $css;
    }

    /**
     * Create CSS style.
     *
<<<<<<< HEAD
     * @return string[]
     */
    private function createCSSStyleFont(Font $font, bool $useDefaults = false, bool $conditional = false): array
=======
     * @return array
     */
    private function createCSSStyleFont(Font $font)
>>>>>>> main
    {
        // Construct CSS
        $css = [];

        // Create CSS
        if ($font->getBold()) {
            $css['font-weight'] = 'bold';
<<<<<<< HEAD
        } elseif ($useDefaults) {
            $css['font-weight'] = 'normal';
=======
>>>>>>> main
        }
        if ($font->getUnderline() != Font::UNDERLINE_NONE && $font->getStrikethrough()) {
            $css['text-decoration'] = 'underline line-through';
        } elseif ($font->getUnderline() != Font::UNDERLINE_NONE) {
            $css['text-decoration'] = 'underline';
        } elseif ($font->getStrikethrough()) {
            $css['text-decoration'] = 'line-through';
<<<<<<< HEAD
        } elseif ($useDefaults) {
            $css['text-decoration'] = 'normal';
        }
        if ($font->getItalic()) {
            $css['font-style'] = 'italic';
        } elseif ($useDefaults) {
            $css['font-style'] = 'normal';
        }

        $css['color'] = '#' . $font->getColor()->getRGB();
        if (!$conditional) {
            $css['font-family'] = '\'' . htmlspecialchars((string) $font->getName(), ENT_QUOTES) . '\'';
            $css['font-size'] = $font->getSize() . 'pt';
        }
=======
        }
        if ($font->getItalic()) {
            $css['font-style'] = 'italic';
        }

        $css['color'] = '#' . $font->getColor()->getRGB();
        $css['font-family'] = '\'' . htmlspecialchars((string) $font->getName(), ENT_QUOTES) . '\'';
        $css['font-size'] = $font->getSize() . 'pt';
>>>>>>> main

        return $css;
    }

    /**
<<<<<<< HEAD
     * @param string[] $css
     */
    private function styleBorder(array &$css, string $index, Border $border): void
    {
        $borderStyle = $border->getBorderStyle();
        // Mpdf doesn't process !important, so omit unimportant border none
        if ($borderStyle === Border::BORDER_NONE && $this instanceof Pdf\Mpdf) {
            return;
        }
        if ($borderStyle !== Border::BORDER_OMIT) {
            $css[$index] = $this->createCSSStyleBorder($border);
        }
    }

    /**
=======
>>>>>>> main
     * Create CSS style.
     *
     * @param Borders $borders Borders
     *
<<<<<<< HEAD
     * @return string[]
     */
    private function createCSSStyleBorders(Borders $borders): array
=======
     * @return array
     */
    private function createCSSStyleBorders(Borders $borders)
>>>>>>> main
    {
        // Construct CSS
        $css = [];

        // Create CSS
<<<<<<< HEAD
        $this->styleBorder($css, 'border-bottom', $borders->getBottom());
        $this->styleBorder($css, 'border-top', $borders->getTop());
        $this->styleBorder($css, 'border-left', $borders->getLeft());
        $this->styleBorder($css, 'border-right', $borders->getRight());
=======
        $css['border-bottom'] = $this->createCSSStyleBorder($borders->getBottom());
        $css['border-top'] = $this->createCSSStyleBorder($borders->getTop());
        $css['border-left'] = $this->createCSSStyleBorder($borders->getLeft());
        $css['border-right'] = $this->createCSSStyleBorder($borders->getRight());
>>>>>>> main

        return $css;
    }

    /**
     * Create CSS style.
     *
     * @param Border $border Border
     */
    private function createCSSStyleBorder(Border $border): string
    {
        //    Create CSS - add !important to non-none border styles for merged cells
        $borderStyle = $this->mapBorderStyle($border->getBorderStyle());

<<<<<<< HEAD
        return $borderStyle . ' #' . $border->getColor()->getRGB() . (($borderStyle === self::BORDER_NONE) ? '' : ' !important');
=======
        return $borderStyle . ' #' . $border->getColor()->getRGB() . (($borderStyle == 'none') ? '' : ' !important');
>>>>>>> main
    }

    /**
     * Create CSS style (Fill).
     *
     * @param Fill $fill Fill
     *
<<<<<<< HEAD
     * @return string[]
     */
    private function createCSSStyleFill(Fill $fill): array
=======
     * @return array
     */
    private function createCSSStyleFill(Fill $fill)
>>>>>>> main
    {
        // Construct HTML
        $css = [];

        // Create CSS
        if ($fill->getFillType() !== Fill::FILL_NONE) {
<<<<<<< HEAD
            if (
                (in_array($fill->getFillType(), ['', Fill::FILL_SOLID], true) || !$fill->getEndColor()->getRGB())
                && $fill->getStartColor()->getRGB()
            ) {
                $value = '#' . $fill->getStartColor()->getRGB();
                $css['background-color'] = $value;
            } elseif ($fill->getEndColor()->getRGB()) {
                $value = '#' . $fill->getEndColor()->getRGB();
                $css['background-color'] = $value;
            }
=======
            $value = $fill->getFillType() == Fill::FILL_NONE ?
                'white' : '#' . $fill->getStartColor()->getRGB();
            $css['background-color'] = $value;
>>>>>>> main
        }

        return $css;
    }

    /**
     * Generate HTML footer.
     */
    public function generateHTMLFooter(): string
    {
        // Construct HTML
        $html = '';
<<<<<<< HEAD
        $html .= '  </body>' . $this->lineEnding;
        $html .= '</html>' . $this->lineEnding;
=======
        $html .= '  </body>' . PHP_EOL;
        $html .= '</html>' . PHP_EOL;
>>>>>>> main

        return $html;
    }

<<<<<<< HEAD
    private function getDir(Worksheet $worksheet): string
    {
        if ($worksheet->getRightToLeft()) {
            return " dir='rtl'";
        }
        if ($this->rtlSheets) {
            return " dir='ltr'";
        }

        return '';
    }

    private function getFloat(Worksheet $worksheet): string
    {
        $float = '';
        if ($worksheet->getRightToLeft()) {
            if ($this->ltrSheets) {
                $float = ' floatright';
            }
        } else {
            if ($this->rtlSheets) {
                $float = ' floatleft';
            }
        }

        return $float;
    }

    private function generateTableTagInline(Worksheet $worksheet, string $id): string
    {
        $style = isset($this->cssStyles['table'])
            ? $this->assembleCSS($this->cssStyles['table']) : '';
        $rtl = $this->getDir($worksheet);
        $float = $this->getFloat($worksheet);
        if (str_ends_with($float, 'right')) {
            $style .= '; float:right';
        } elseif (str_ends_with($float, 'left')) {
            $style .= '; float:left';
        }
        $prntgrid = $worksheet->getPrintGridlines();
        $viewgrid = $this->isPdf ? $prntgrid : $worksheet->getShowGridlines();
        $printArea = $worksheet->getPageSetup()->getPrintArea();
        $dataPrint = ($printArea === '') ? '' : (" data-printarea='" . htmlspecialchars($printArea) . "'");
        if ($viewgrid && $prntgrid) {
            $html = "    <table$rtl$dataPrint $id style='$style' class='gridlines gridlinesp'>" . $this->lineEnding;
        } elseif ($viewgrid) {
            $html = "    <table$rtl$dataPrint $id style='$style' class='gridlines'>" . $this->lineEnding;
        } elseif ($prntgrid) {
            $html = "    <table$rtl$dataPrint $id style='$style' class='gridlinesp'>" . $this->lineEnding;
        } else {
            $html = "    <table$rtl$dataPrint $id style='$style'>" . $this->lineEnding;
=======
    private function generateTableTagInline(Worksheet $worksheet, string $id): string
    {
        $style = isset($this->cssStyles['table']) ?
            $this->assembleCSS($this->cssStyles['table']) : '';

        $prntgrid = $worksheet->getPrintGridlines();
        $viewgrid = $this->isPdf ? $prntgrid : $worksheet->getShowGridlines();
        if ($viewgrid && $prntgrid) {
            $html = "    <table border='1' cellpadding='1' $id cellspacing='1' style='$style' class='gridlines gridlinesp'>" . PHP_EOL;
        } elseif ($viewgrid) {
            $html = "    <table border='0' cellpadding='0' $id cellspacing='0' style='$style' class='gridlines'>" . PHP_EOL;
        } elseif ($prntgrid) {
            $html = "    <table border='0' cellpadding='0' $id cellspacing='0' style='$style' class='gridlinesp'>" . PHP_EOL;
        } else {
            $html = "    <table border='0' cellpadding='1' $id cellspacing='0' style='$style'>" . PHP_EOL;
>>>>>>> main
        }

        return $html;
    }

    private function generateTableTag(Worksheet $worksheet, string $id, string &$html, int $sheetIndex): void
    {
        if (!$this->useInlineCss) {
<<<<<<< HEAD
            $rtl = $this->getDir($worksheet);
            $printArea = $worksheet->getPageSetup()->getPrintArea();
            $dataPrint = ($printArea === '') ? '' : (" data-printarea='" . htmlspecialchars($printArea) . "'");
            $float = $this->getFloat($worksheet);
            if ($this instanceof Pdf\Dompdf) {
                $gridlines = $worksheet->getPrintGridlines() ? ' gridlines' : '';
                $gridlinesp = $worksheet->getPrintGridlines() ? ' gridlinesp' : '';
            } else {
                $gridlines = $worksheet->getShowGridlines() ? ' gridlines' : '';
                $gridlinesp = $worksheet->getPrintGridlines() ? ' gridlinesp' : '';
            }
            $html .= "    <table$rtl$dataPrint $id class='sheet$sheetIndex$gridlines$gridlinesp$float'>" . $this->lineEnding;
=======
            $gridlines = $worksheet->getShowGridlines() ? ' gridlines' : '';
            $gridlinesp = $worksheet->getPrintGridlines() ? ' gridlinesp' : '';
            $html .= "    <table border='0' cellpadding='0' cellspacing='0' $id class='sheet$sheetIndex$gridlines$gridlinesp'>" . PHP_EOL;
>>>>>>> main
        } else {
            $html .= $this->generateTableTagInline($worksheet, $id);
        }
    }

    /**
     * Generate table header.
     *
     * @param Worksheet $worksheet The worksheet for the table we are writing
     * @param bool $showid whether or not to add id to table tag
<<<<<<< HEAD
     */
    private function generateTableHeader(Worksheet $worksheet, bool $showid = true): string
=======
     *
     * @return string
     */
    private function generateTableHeader(Worksheet $worksheet, $showid = true)
>>>>>>> main
    {
        $sheetIndex = $worksheet->getParentOrThrow()->getIndex($worksheet);

        // Construct HTML
        $html = '';
        $id = $showid ? "id='sheet$sheetIndex'" : '';
<<<<<<< HEAD
        $clear = ($this->rtlSheets && $this->ltrSheets) ? '; clear:both' : '';

        if ($showid) {
            $html .= "<div style='page: page$sheetIndex$clear'>" . $this->lineEnding;
        } else {
            $html .= "<div style='page: page$sheetIndex$clear' class='scrpgbrk'>" . $this->lineEnding;
=======
        if ($showid) {
            $html .= "<div style='page: page$sheetIndex'>" . PHP_EOL;
        } else {
            $html .= "<div style='page: page$sheetIndex' class='scrpgbrk'>" . PHP_EOL;
>>>>>>> main
        }

        $this->generateTableTag($worksheet, $id, $html, $sheetIndex);

        // Write <col> elements
        $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestColumn()) - 1;
        $i = -1;
        while ($i++ < $highestColumnIndex) {
            if (!$this->useInlineCss) {
<<<<<<< HEAD
                $html .= '        <col class="col' . $i . '" />' . $this->lineEnding;
            } else {
                $style = isset($this->cssStyles['table.sheet' . $sheetIndex . ' col.col' . $i])
                    ? $this->assembleCSS($this->cssStyles['table.sheet' . $sheetIndex . ' col.col' . $i]) : '';
                $html .= '        <col style="' . $style . '" />' . $this->lineEnding;
=======
                $html .= '        <col class="col' . $i . '" />' . PHP_EOL;
            } else {
                $style = isset($this->cssStyles['table.sheet' . $sheetIndex . ' col.col' . $i]) ?
                    $this->assembleCSS($this->cssStyles['table.sheet' . $sheetIndex . ' col.col' . $i]) : '';
                $html .= '        <col style="' . $style . '" />' . PHP_EOL;
>>>>>>> main
            }
        }

        return $html;
    }

    /**
     * Generate table footer.
     */
    private function generateTableFooter(): string
    {
<<<<<<< HEAD
        return '    </tbody></table>' . $this->lineEnding . '</div>' . $this->lineEnding;
=======
        return '    </tbody></table>' . PHP_EOL . '</div>' . PHP_EOL;
>>>>>>> main
    }

    /**
     * Generate row start.
     *
     * @param int $sheetIndex Sheet index (0-based)
     * @param int $row row number
<<<<<<< HEAD
     */
    private function generateRowStart(Worksheet $worksheet, int $sheetIndex, int $row): string
=======
     *
     * @return string
     */
    private function generateRowStart(Worksheet $worksheet, $sheetIndex, $row)
>>>>>>> main
    {
        $html = '';
        if (count($worksheet->getBreaks()) > 0) {
            $breaks = $worksheet->getRowBreaks();

            // check if a break is needed before this row
            if (isset($breaks['A' . $row])) {
                // close table: </table>
                $html .= $this->generateTableFooter();
                if ($this->isPdf && $this->useInlineCss) {
                    $html .= '<div style="page-break-before:always" />';
                }

                // open table again: <table> + <col> etc.
                $html .= $this->generateTableHeader($worksheet, false);
<<<<<<< HEAD
                $html .= '<tbody>' . $this->lineEnding;
=======
                $html .= '<tbody>' . PHP_EOL;
>>>>>>> main
            }
        }

        // Write row start
        if (!$this->useInlineCss) {
<<<<<<< HEAD
            $html .= '          <tr class="row' . $row . '">' . $this->lineEnding;
=======
            $html .= '          <tr class="row' . $row . '">' . PHP_EOL;
>>>>>>> main
        } else {
            $style = isset($this->cssStyles['table.sheet' . $sheetIndex . ' tr.row' . $row])
                ? $this->assembleCSS($this->cssStyles['table.sheet' . $sheetIndex . ' tr.row' . $row]) : '';

<<<<<<< HEAD
            if ($style === '') {
                $html .= '          <tr>' . $this->lineEnding;
            } else {
                $html .= '          <tr style="' . $style . '">' . $this->lineEnding;
            }
=======
            $html .= '          <tr style="' . $style . '">' . PHP_EOL;
>>>>>>> main
        }

        return $html;
    }

<<<<<<< HEAD
    /** @return array{null|''|Cell, array{}|string, non-empty-string} */
=======
>>>>>>> main
    private function generateRowCellCss(Worksheet $worksheet, string $cellAddress, int $row, int $columnNumber): array
    {
        $cell = ($cellAddress > '') ? $worksheet->getCellCollection()->get($cellAddress) : '';
        $coordinate = Coordinate::stringFromColumnIndex($columnNumber + 1) . ($row + 1);
        if (!$this->useInlineCss) {
            $cssClass = 'column' . $columnNumber;
        } else {
            $cssClass = [];
<<<<<<< HEAD
=======
            // The statements below do nothing.
            // Commenting out the code rather than deleting it
            // in case someone can figure out what their intent was.
            //if ($cellType == 'th') {
            //    if (isset($this->cssStyles['table.sheet' . $sheetIndex . ' th.column' . $colNum])) {
            //        $this->cssStyles['table.sheet' . $sheetIndex . ' th.column' . $colNum];
            //    }
            //} else {
            //    if (isset($this->cssStyles['table.sheet' . $sheetIndex . ' td.column' . $colNum])) {
            //        $this->cssStyles['table.sheet' . $sheetIndex . ' td.column' . $colNum];
            //    }
            //}
            // End of mystery statements.
>>>>>>> main
        }

        return [$cell, $cssClass, $coordinate];
    }

<<<<<<< HEAD
    private function generateRowCellDataValueRich(RichText $richText, ?Font $defaultFont = null): string
    {
        $cellData = '';
        // Loop through rich text elements
        $elements = $richText->getRichTextElements();
        foreach ($elements as $element) {
            // Rich text start?
            $font = ($element instanceof Run) ? $element->getFont() : $defaultFont;
            if ($element instanceof Run || $font !== null) {
                $cellEnd = '';
                if ($font !== null) {
                    $cellData .= '<span style="' . $this->assembleCSS($this->createCSSStyleFont($font, true)) . '">';

                    if ($font->getSuperscript()) {
                        $cellData .= '<sup>';
                        $cellEnd = '</sup>';
                    } elseif ($font->getSubscript()) {
                        $cellData .= '<sub>';
                        $cellEnd = '</sub>';
                    }
                } else {
                    $cellData .= '<span>';
=======
    private function generateRowCellDataValueRich(Cell $cell, string &$cellData): void
    {
        // Loop through rich text elements
        $elements = $cell->getValue()->getRichTextElements();
        foreach ($elements as $element) {
            // Rich text start?
            if ($element instanceof Run) {
                $cellEnd = '';
                if ($element->getFont() !== null) {
                    $cellData .= '<span style="' . $this->assembleCSS($this->createCSSStyleFont($element->getFont())) . '">';

                    if ($element->getFont()->getSuperscript()) {
                        $cellData .= '<sup>';
                        $cellEnd = '</sup>';
                    } elseif ($element->getFont()->getSubscript()) {
                        $cellData .= '<sub>';
                        $cellEnd = '</sub>';
                    }
>>>>>>> main
                }

                // Convert UTF8 data to PCDATA
                $cellText = $element->getText();
                $cellData .= htmlspecialchars($cellText, Settings::htmlEntityFlags());

                $cellData .= $cellEnd;

                $cellData .= '</span>';
            } else {
                // Convert UTF8 data to PCDATA
                $cellText = $element->getText();
                $cellData .= htmlspecialchars($cellText, Settings::htmlEntityFlags());
            }
        }
<<<<<<< HEAD

        return self::nl2brx($cellData);
=======
>>>>>>> main
    }

    private function generateRowCellDataValue(Worksheet $worksheet, Cell $cell, string &$cellData): void
    {
        if ($cell->getValue() instanceof RichText) {
<<<<<<< HEAD
            $cellData .= $this->generateRowCellDataValueRich($cell->getValue(), $cell->getStyle()->getFont());
        } else {
            if ($this->preCalculateFormulas) {
                try {
                    $origData = $cell->getCalculatedValue();
                } catch (CalculationException) {
                    $origData = '#ERROR'; // mark as error, rather than crash everything
                }
                if ($this->betterBoolean && is_bool($origData)) {
                    if ($cell->getStyle()->getCheckbox()) {
                        $origData2 = $origData ? '☑' : '☐';
                    } else {
                        $origData2 = $origData ? $this->getTrue : $this->getFalse;
                    }
                } else {
                    try {
                        $origData2 = $cell->getCalculatedValueString();
                    } catch (CalculationException) {
                        $origData2 = '#ERROR'; // mark as error, rather than crash everything
                    }
                }
            } else {
                $origData = $cell->getValue();
                if ($this->betterBoolean && is_bool($origData)) {
                    if ($cell->getStyle()->getCheckbox()) {
                        $origData2 = $origData ? '☑' : '☐';
                    } else {
                        $origData2 = $origData ? $this->getTrue : $this->getFalse;
                    }
                } else {
                    $origData2 = $cell->getValueString();
                }
            }
            $formatCode = $worksheet->getParentOrThrow()->getCellXfByIndex($cell->getXfIndex())->getNumberFormat()->getFormatCode();

            $cellData = NumberFormat::toFormattedString(
                $origData2,
=======
            $this->generateRowCellDataValueRich($cell, $cellData);
        } else {
            $origData = $this->preCalculateFormulas ? $cell->getCalculatedValue() : $cell->getValue();
            $formatCode = $worksheet->getParentOrThrow()->getCellXfByIndex($cell->getXfIndex())->getNumberFormat()->getFormatCode();

            $cellData = NumberFormat::toFormattedString(
                $origData ?? '',
>>>>>>> main
                $formatCode ?? NumberFormat::FORMAT_GENERAL,
                [$this, 'formatColor']
            );

            if ($cellData === $origData) {
                $cellData = htmlspecialchars($cellData, Settings::htmlEntityFlags());
            }
            if ($worksheet->getParentOrThrow()->getCellXfByIndex($cell->getXfIndex())->getFont()->getSuperscript()) {
                $cellData = '<sup>' . $cellData . '</sup>';
            } elseif ($worksheet->getParentOrThrow()->getCellXfByIndex($cell->getXfIndex())->getFont()->getSubscript()) {
                $cellData = '<sub>' . $cellData . '</sub>';
            }
        }
    }

<<<<<<< HEAD
    /** @param string|string[] $cssClass */
    private function generateRowCellData(Worksheet $worksheet, null|Cell|string $cell, array|string &$cssClass): string
    {
=======
    /**
     * @param null|Cell|string $cell
     * @param array|string $cssClass
     */
    private function generateRowCellData(Worksheet $worksheet, $cell, &$cssClass, string $cellType): string
    {
        $cellData = '&nbsp;';
>>>>>>> main
        if ($cell instanceof Cell) {
            $cellData = '';
            // Don't know what this does, and no test cases.
            //if ($cell->getParent() === null) {
            //    $cell->attach($worksheet);
            //}
            // Value
            $this->generateRowCellDataValue($worksheet, $cell, $cellData);

            // Converts the cell content so that spaces occuring at beginning of each new line are replaced by &nbsp;
            // Example: "  Hello\n to the world" is converted to "&nbsp;&nbsp;Hello\n&nbsp;to the world"
<<<<<<< HEAD
            $cellData = Preg::replace('/(?m)(?:^|\G) /', '&nbsp;', $cellData);

            // convert newline "\n" to '<br>'
            $cellData = self::nl2brx($cellData);

            // Extend CSS class?
            $dataType = $cell->getDataType();
            if ($this->betterBoolean && $this->preCalculateFormulas && $dataType === DataType::TYPE_FORMULA) {
                try {
                    $calculatedValue = $cell->getCalculatedValue();
                    if (is_bool($calculatedValue)) {
                        $dataType = DataType::TYPE_BOOL;
                    } elseif (is_numeric($calculatedValue)) {
                        $dataType = DataType::TYPE_NUMERIC;
                    } elseif (is_string($calculatedValue)) {
                        $dataType = DataType::TYPE_STRING;
                    }
                } catch (CalculationException $exception) {
                    $calculatedValue = '#ERROR';
                    $dataType = DataType::TYPE_ERROR;
                }
            }
            if (!$this->useInlineCss && is_string($cssClass)) {
                $cssClass .= ' style' . $cell->getXfIndex();
                $cssClass .= ' ' . $dataType;
            } elseif (is_array($cssClass)) {
                $index = $cell->getXfIndex();
                $styleIndex = 'td.style' . $index . ', th.style' . $index;
                if (isset($this->cssStyles[$styleIndex])) {
                    $cssClass = array_merge($cssClass, $this->cssStyles[$styleIndex]);
=======
            $cellData = (string) preg_replace('/(?m)(?:^|\G) /', '&nbsp;', $cellData);

            // convert newline "\n" to '<br>'
            $cellData = nl2br($cellData);

            // Extend CSS class?
            if (!$this->useInlineCss && is_string($cssClass)) {
                $cssClass .= ' style' . $cell->getXfIndex();
                $cssClass .= ' ' . $cell->getDataType();
            } elseif (is_array($cssClass)) {
                if ($cellType == 'th') {
                    if (isset($this->cssStyles['th.style' . $cell->getXfIndex()])) {
                        $cssClass = array_merge($cssClass, $this->cssStyles['th.style' . $cell->getXfIndex()]);
                    }
                } else {
                    if (isset($this->cssStyles['td.style' . $cell->getXfIndex()])) {
                        $cssClass = array_merge($cssClass, $this->cssStyles['td.style' . $cell->getXfIndex()]);
                    }
>>>>>>> main
                }

                // General horizontal alignment: Actual horizontal alignment depends on dataType
                $sharedStyle = $worksheet->getParentOrThrow()->getCellXfByIndex($cell->getXfIndex());
                if (
                    $sharedStyle->getAlignment()->getHorizontal() == Alignment::HORIZONTAL_GENERAL
                    && isset($this->cssStyles['.' . $cell->getDataType()]['text-align'])
                ) {
<<<<<<< HEAD
                    $cssClass['text-align'] = $this->cssStyles['.' . $dataType]['text-align'];
                }
            }
        } else {
            $cellData = "$cell";
=======
                    $cssClass['text-align'] = $this->cssStyles['.' . $cell->getDataType()]['text-align'];
                }
            }
        } else {
>>>>>>> main
            // Use default borders for empty cell
            if (is_string($cssClass)) {
                $cssClass .= ' style0';
            }
        }
<<<<<<< HEAD
        /*
         * Browsers may remove an entirely empty row.
         * An interesting option is to leave an empty cell empty using css.
         * td:empty::after{content: "\00a0";}
         * This works well in modern browsers.
         * Alas, none of our Pdf writers can handle it.
         */

        return (trim($cellData) === '') ? '&nbsp;' : $cellData;
=======

        return $cellData;
>>>>>>> main
    }

    private function generateRowIncludeCharts(Worksheet $worksheet, string $coordinate): string
    {
        return $this->includeCharts ? $this->writeChartInCell($worksheet, $coordinate) : '';
    }

    private function generateRowSpans(string $html, int $rowSpan, int $colSpan): string
    {
        $html .= ($colSpan > 1) ? (' colspan="' . $colSpan . '"') : '';
        $html .= ($rowSpan > 1) ? (' rowspan="' . $rowSpan . '"') : '';

        return $html;
    }

    /**
<<<<<<< HEAD
     * @param string|string[] $cssClass
     * @param Conditional[] $condStyles
     */
    private function generateRowWriteCell(
        string &$html,
        Worksheet $worksheet,
        string $coordinate,
        string $cellType,
        string $cellData,
        int $colSpan,
        int $rowSpan,
        array|string $cssClass,
        int $colNum,
        int $sheetIndex,
        int $row,
        array $condStyles = []
    ): void {
        // Image?
        $htmlx = $this->writeImageInCell($coordinate);
=======
     * @param array|string $cssClass
     */
    private function generateRowWriteCell(string &$html, Worksheet $worksheet, string $coordinate, string $cellType, string $cellData, int $colSpan, int $rowSpan, $cssClass, int $colNum, int $sheetIndex, int $row): void
    {
        // Image?
        $htmlx = $this->writeImageInCell($worksheet, $coordinate);
>>>>>>> main
        // Chart?
        $htmlx .= $this->generateRowIncludeCharts($worksheet, $coordinate);
        // Column start
        $html .= '            <' . $cellType;
<<<<<<< HEAD
        if ($worksheet->getStyle($coordinate)->getCheckbox()) {
            $html .= ' data-checkbox="1"';
        }
        $dataType = $worksheet->getCell($coordinate)->getDataType();
        if ($this->betterBoolean) {
            if ($dataType === DataType::TYPE_BOOL) {
                $html .= ' data-type="' . DataType::TYPE_BOOL . '"';
            } elseif ($dataType === DataType::TYPE_FORMULA && $this->preCalculateFormulas) {
                try {
                    $calculatedValue = $worksheet
                        ->getCell($coordinate)
                        ->getCalculatedValue();
                    if (is_bool($calculatedValue)) {
                        $html .= ' data-type="' . DataType::TYPE_BOOL . '"';
                    } elseif ($this->dataFormula && is_string($calculatedValue)) {
                        $html .= ' data-type="' . DataType::TYPE_STRING . '"';
                    } elseif ($this->dataFormula && (is_int($calculatedValue) || is_float($calculatedValue))) {
                        $html .= ' data-type="' . DataType::TYPE_NUMERIC . '"';
                    }
                } catch (CalculationException) {
                    $html .= ' data-type="' . DataType::TYPE_ERROR . '"';
                }
            } elseif (is_numeric($cellData) && $worksheet->getCell($coordinate)->getDataType() === DataType::TYPE_STRING) {
                $html .= ' data-type="' . DataType::TYPE_STRING . '"';
            }
        }
        if ($dataType === DataType::TYPE_FORMULA && $this->dataFormula) {
            if ($this->preCalculateFormulas) {
                $html .= ' data-formula="'
                . htmlspecialchars(
                    $worksheet->getCell($coordinate)
                        ->getValueString()
                )
                . '"';
            }
        }
        $holdCss = '';
=======
>>>>>>> main
        if (!$this->useInlineCss && !$this->isPdf && is_string($cssClass)) {
            $html .= ' class="' . $cssClass . '"';
            if ($htmlx) {
                $html .= " style='position: relative;'";
            }
        } else {
            //** Necessary redundant code for the sake of \PhpOffice\PhpSpreadsheet\Writer\Pdf **
            // We must explicitly write the width of the <td> element because TCPDF
            // does not recognize e.g. <col style="width:42pt">
            if ($this->useInlineCss) {
                $xcssClass = is_array($cssClass) ? $cssClass : [];
            } else {
                if (is_string($cssClass)) {
                    $html .= ' class="' . $cssClass . '"';
                }
                $xcssClass = [];
            }
            $width = 0;
            $i = $colNum - 1;
            $e = $colNum + $colSpan - 1;
            while ($i++ < $e) {
                if (isset($this->columnWidths[$sheetIndex][$i])) {
                    $width += $this->columnWidths[$sheetIndex][$i];
                }
            }
            $xcssClass['width'] = (string) $width . 'pt';
            // We must also explicitly write the height of the <td> element because TCPDF
            // does not recognize e.g. <tr style="height:50pt">
            if (isset($this->cssStyles['table.sheet' . $sheetIndex . ' tr.row' . $row]['height'])) {
                $height = $this->cssStyles['table.sheet' . $sheetIndex . ' tr.row' . $row]['height'];
                $xcssClass['height'] = $height;
            }
            //** end of redundant code **
<<<<<<< HEAD
            if ($this->useInlineCss) {
                foreach (['border-top', 'border-bottom', 'border-right', 'border-left'] as $borderType) {
                    if (($xcssClass[$borderType] ?? '') === 'none #000000') {
                        unset($xcssClass[$borderType]);
                    }
                }
                $foundBorder = false;
                if ($this instanceof Pdf\Tcpdf && $worksheet->getPrintGridLines()) {
                    foreach (['border-top', 'border-bottom', 'border-right', 'border-left'] as $borderType) {
                        if (isset($xcssClass[$borderType])) {
                            $foundBorder = true;
                        }
                    }
                    if (!$foundBorder) {
                        $xcssClass['border'] = '0.1px solid black';
                    }
                }
            }
=======
>>>>>>> main

            if ($htmlx) {
                $xcssClass['position'] = 'relative';
            }
<<<<<<< HEAD
            /** @var string[] $xcssClass */
            $holdCss = $this->assembleCSS($xcssClass);
            if ($this->useInlineCss) {
                $prntgrid = $worksheet->getPrintGridlines();
                $viewgrid = $this->isPdf ? $prntgrid : $worksheet->getShowGridlines();
                if ($viewgrid && $prntgrid) {
                    $html .= ' class="gridlines gridlinesp"';
                } elseif ($viewgrid) {
                    $html .= ' class="gridlines"';
                } elseif ($prntgrid) {
                    $html .= ' class="gridlinesp"';
                }
            }
        }

        $html = $this->generateRowSpans($html, $rowSpan, $colSpan);

        $mergedCellStyle = new MergedCellStyle();
        $mergedStyle = $mergedCellStyle->getMergedStyle(
            $worksheet,
            $coordinate,
            $this->tableFormats,
            $this->conditionalFormatting,
            $this->tableFormatsBuiltin
        );
        if ($mergedCellStyle->getMatched()) {
            $styles = $this->createCSSStyle($mergedStyle, true);
            $html .= ' style="';
            if ($holdCss !== '') {
                $html .= "$holdCss; ";
                $holdCss = '';
            }
            foreach ($styles as $key => $value) {
                if (!str_starts_with($key, 'border-') || $value !== 'none #000000') {
                    $html .= $key . ':' . $value . ';';
                }
            }
            $html .= '"';
        }
        if ($holdCss !== '') {
            $html .= ' style="' . $holdCss . '"';
        }

=======
            $html .= ' style="' . $this->assembleCSS($xcssClass) . '"';
        }
        $html = $this->generateRowSpans($html, $rowSpan, $colSpan);

>>>>>>> main
        $html .= '>';
        $html .= $htmlx;

        $html .= $this->writeComment($worksheet, $coordinate);

        // Cell data
        $html .= $cellData;

        // Column end
<<<<<<< HEAD
        $html .= '</' . $cellType . '>' . $this->lineEnding;
=======
        $html .= '</' . $cellType . '>' . PHP_EOL;
>>>>>>> main
    }

    /**
     * Generate row.
     *
<<<<<<< HEAD
     * @param array<int, string> $values Array containing cells in a row
     * @param int $row Row number (0-based)
     * @param string $cellType eg: 'td'
     */
    private function generateRow(Worksheet $worksheet, array $values, int $row, string $cellType): string
=======
     * @param array $values Array containing cells in a row
     * @param int $row Row number (0-based)
     * @param string $cellType eg: 'td'
     *
     * @return string
     */
    private function generateRow(Worksheet $worksheet, array $values, $row, $cellType)
>>>>>>> main
    {
        // Sheet index
        $sheetIndex = $worksheet->getParentOrThrow()->getIndex($worksheet);
        $html = $this->generateRowStart($worksheet, $sheetIndex, $row);
<<<<<<< HEAD

        // Write cells
        $colNum = 0;
        $tcpdfInited = false;
        foreach ($values as $key => $cellAddress) {
            if ($this instanceof Pdf\Mpdf) {
                $colNum = $key - 1;
            } elseif ($this instanceof Pdf\Tcpdf) {
                // It appears that Tcpdf requires first cell in tr.
                $colNum = $key - 1;
                if (!$tcpdfInited && $key !== 1) {
                    $tempspan = ($colNum > 1) ? " colspan='$colNum'" : '';
                    $html .= "<td$tempspan></td>" . $this->lineEnding;
                }
                $tcpdfInited = true;
            }
            [$cell, $cssClass, $coordinate] = $this->generateRowCellCss($worksheet, $cellAddress, $row, $colNum);

            // Cell Data
            $cellData = $this->generateRowCellData($worksheet, $cell, $cssClass);

            // Get an array of all styles
            $condStyles = $worksheet->getStyle($coordinate)->getConditionalStyles();
=======
        $generateDiv = $this->isMPdf && $worksheet->getRowDimension($row + 1)->getVisible() === false;
        if ($generateDiv) {
            $html .= '<div style="visibility:hidden; display:none;">' . PHP_EOL;
        }

        // Write cells
        $colNum = 0;
        foreach ($values as $cellAddress) {
            [$cell, $cssClass, $coordinate] = $this->generateRowCellCss($worksheet, $cellAddress, $row, $colNum);

            // Cell Data
            $cellData = $this->generateRowCellData($worksheet, $cell, $cssClass, $cellType);
>>>>>>> main

            // Hyperlink?
            if ($worksheet->hyperlinkExists($coordinate) && !$worksheet->getHyperlink($coordinate)->isInternal()) {
                $url = $worksheet->getHyperlink($coordinate)->getUrl();
                $urlDecode1 = html_entity_decode($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
<<<<<<< HEAD
                $urlTrim = Preg::replace('/^\s+/u', '', $urlDecode1);
                $parseScheme = Preg::isMatch('/^([\w\s\x00-\x1f]+):/u', strtolower($urlTrim), $matches);
                if ($parseScheme && !in_array($matches[1], ['http', 'https', 'file', 'ftp', 'mailto', 's3'], true)) {
                    $cellData = htmlspecialchars($url, Settings::htmlEntityFlags());
                    $cellData = self::replaceControlChars($cellData);
                } else {
                    $tooltip = $worksheet->getHyperlink($coordinate)->getTooltip();
                    $tooltipOut = empty($tooltip) ? '' : (' title="' . htmlspecialchars($tooltip) . '"');
                    $cellData = '<a href="'
                        . htmlspecialchars($url) . '"'
                        . $tooltipOut
                        . '>' . $cellData . '</a>';
=======
                $urlTrim = preg_replace('/^\s+/u', '', $urlDecode1) ?? $urlDecode1;
                $parseScheme = preg_match('/^([\w\s\x00-\x1f]+):/u', strtolower($urlTrim), $matches);
                if ($parseScheme === 1 && !in_array($matches[1], ['http', 'https', 'file', 'ftp', 'mailto', 's3'], true)) {
                    $cellData = htmlspecialchars($url, Settings::htmlEntityFlags());
                    $cellData = self::replaceControlChars($cellData);
                } else {
                    $cellData = '<a href="' . htmlspecialchars($url, Settings::htmlEntityFlags()) . '" title="' . htmlspecialchars($worksheet->getHyperlink($coordinate)->getTooltip(), Settings::htmlEntityFlags()) . '">' . $cellData . '</a>';
>>>>>>> main
                }
            }

            // Should the cell be written or is it swallowed by a rowspan or colspan?
            $writeCell = !(isset($this->isSpannedCell[$worksheet->getParentOrThrow()->getIndex($worksheet)][$row + 1][$colNum])
                && $this->isSpannedCell[$worksheet->getParentOrThrow()->getIndex($worksheet)][$row + 1][$colNum]);

            // Colspan and Rowspan
            $colSpan = 1;
            $rowSpan = 1;
            if (isset($this->isBaseCell[$worksheet->getParentOrThrow()->getIndex($worksheet)][$row + 1][$colNum])) {
<<<<<<< HEAD
                /** @var array<string, int> */
=======
>>>>>>> main
                $spans = $this->isBaseCell[$worksheet->getParentOrThrow()->getIndex($worksheet)][$row + 1][$colNum];
                $rowSpan = $spans['rowspan'];
                $colSpan = $spans['colspan'];

                //    Also apply style from last cell in merge to fix borders -
                //        relies on !important for non-none border declarations in createCSSStyleBorder
                $endCellCoord = Coordinate::stringFromColumnIndex($colNum + $colSpan) . ($row + $rowSpan);
<<<<<<< HEAD
                if (!$this->useInlineCss && is_string($cssClass)) {
                    $cssClass .= ' style' . $worksheet->getCell($endCellCoord)->getXfIndex();
                } else {
                    $endBorders = $this->spreadsheet->getCellXfByIndex($worksheet->getCell($endCellCoord)->getXfIndex())->getBorders();
                    $altBorders = $this->createCSSStyleBorders($endBorders);
                    foreach ($altBorders as $altKey => $altValue) {
                        if (str_contains($altValue, '!important')) {
                            $cssClass[$altKey] = $altValue;
                        }
                    }
=======
                if (!$this->useInlineCss) {
                    $cssClass .= ' style' . $worksheet->getCell($endCellCoord)->getXfIndex();
>>>>>>> main
                }
            }

            // Write
            if ($writeCell) {
<<<<<<< HEAD
                $this->generateRowWriteCell($html, $worksheet, $coordinate, $cellType, $cellData, $colSpan, $rowSpan, $cssClass, $colNum, $sheetIndex, $row, $condStyles);
=======
                $this->generateRowWriteCell($html, $worksheet, $coordinate, $cellType, $cellData, $colSpan, $rowSpan, $cssClass, $colNum, $sheetIndex, $row);
>>>>>>> main
            }

            // Next column
            ++$colNum;
        }
<<<<<<< HEAD
        if ($this instanceof Pdf\Tcpdf) {
            if (str_ends_with($html, '<tr>' . $this->lineEnding)) {
                $html .= '<td>&nbsp;</td>' . $this->lineEnding;
            }
        }

        // Write row end
        $html .= '          </tr>' . $this->lineEnding;
=======

        // Write row end
        if ($generateDiv) {
            $html .= '</div>' . PHP_EOL;
        }
        $html .= '          </tr>' . PHP_EOL;
>>>>>>> main

        // Return
        return $html;
    }

<<<<<<< HEAD
    private static function replaceControlChars(string $convert): string
    {
        return Preg::replaceCallback(
            '/[\x00-\x1f]/',
            fn (array $matches) => '&#' . ord($matches[0]) . ';',
=======
    private static function replaceNonAscii(array $matches): string
    {
        return '&#' . mb_ord($matches[0], 'UTF-8') . ';';
    }

    private static function replaceControlChars(string $convert): string
    {
        return (string) preg_replace_callback(
            '/[\x00-\x1f]/',
            [self::class, 'replaceNonAscii'],
>>>>>>> main
            $convert
        );
    }

    /**
     * Takes array where of CSS properties / values and converts to CSS string.
     *
<<<<<<< HEAD
     * @param string[] $values
     */
    private function assembleCSS(array $values = []): string
=======
     * @return string
     */
    private function assembleCSS(array $values = [])
>>>>>>> main
    {
        $pairs = [];
        foreach ($values as $property => $value) {
            $pairs[] = $property . ':' . $value;
        }
        $string = implode('; ', $pairs);

        return $string;
    }

    /**
     * Get images root.
<<<<<<< HEAD
     */
    public function getImagesRoot(): string
=======
     *
     * @return string
     */
    public function getImagesRoot()
>>>>>>> main
    {
        return $this->imagesRoot;
    }

    /**
     * Set images root.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setImagesRoot(string $imagesRoot): static
=======
     * @param string $imagesRoot
     *
     * @return $this
     */
    public function setImagesRoot($imagesRoot)
>>>>>>> main
    {
        $this->imagesRoot = $imagesRoot;

        return $this;
    }

    /**
     * Get embed images.
<<<<<<< HEAD
     */
    public function getEmbedImages(): bool
=======
     *
     * @return bool
     */
    public function getEmbedImages()
>>>>>>> main
    {
        return $this->embedImages;
    }

    /**
     * Set embed images.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setEmbedImages(bool $embedImages): static
=======
     * @param bool $embedImages
     *
     * @return $this
     */
    public function setEmbedImages($embedImages)
>>>>>>> main
    {
        $this->embedImages = $embedImages;

        return $this;
    }

    /**
     * Get use inline CSS?
<<<<<<< HEAD
     */
    public function getUseInlineCss(): bool
=======
     *
     * @return bool
     */
    public function getUseInlineCss()
>>>>>>> main
    {
        return $this->useInlineCss;
    }

    /**
     * Set use inline CSS?
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setUseInlineCss(bool $useInlineCss): static
=======
     * @param bool $useInlineCss
     *
     * @return $this
     */
    public function setUseInlineCss($useInlineCss)
>>>>>>> main
    {
        $this->useInlineCss = $useInlineCss;

        return $this;
    }

<<<<<<< HEAD
    public function getTableFormats(): bool
    {
        return $this->tableFormats;
    }

    public function setTableFormats(bool $tableFormats, ?bool $tableFormatsBuiltin = null): self
    {
        $this->tableFormats = $tableFormats;
        $this->tableFormatsBuiltin = $tableFormatsBuiltin;

        return $this;
    }

    public function getConditionalFormatting(): bool
    {
        return $this->conditionalFormatting;
    }

    public function setConditionalFormatting(bool $conditionalFormatting): self
    {
        $this->conditionalFormatting = $conditionalFormatting;
=======
    /**
     * Get use embedded CSS?
     *
     * @return bool
     *
     * @codeCoverageIgnore
     *
     * @deprecated no longer used
     */
    public function getUseEmbeddedCSS()
    {
        return $this->useEmbeddedCSS;
    }

    /**
     * Set use embedded CSS?
     *
     * @param bool $useEmbeddedCSS
     *
     * @return $this
     *
     * @codeCoverageIgnore
     *
     * @deprecated no longer used
     */
    public function setUseEmbeddedCSS($useEmbeddedCSS)
    {
        $this->useEmbeddedCSS = $useEmbeddedCSS;
>>>>>>> main

        return $this;
    }

    /**
     * Add color to formatted string as inline style.
     *
     * @param string $value Plain formatted value without color
     * @param string $format Format code
<<<<<<< HEAD
     */
    public function formatColor(string $value, string $format): string
    {
        return self::formatColorStatic($value, $format);
    }

    /**
     * Add color to formatted string as inline style.
     *
     * @param string $value Plain formatted value without color
     * @param string $format Format code
     */
    public static function formatColorStatic(string $value, string $format): string
=======
     *
     * @return string
     */
    public function formatColor($value, $format)
>>>>>>> main
    {
        // Color information, e.g. [Red] is always at the beginning
        $color = null; // initialize
        $matches = [];

        $color_regex = '/^\[[a-zA-Z]+\]/';
<<<<<<< HEAD
        if (Preg::isMatch($color_regex, $format, $matches)) {
=======
        if (preg_match($color_regex, $format, $matches)) {
>>>>>>> main
            $color = str_replace(['[', ']'], '', $matches[0]);
            $color = strtolower($color);
        }

        // convert to PCDATA
<<<<<<< HEAD
        $result = htmlspecialchars($value, Settings::htmlEntityFlags());
=======
        $result = htmlspecialchars($value, ENT_NOQUOTES);
>>>>>>> main

        // color span tag
        if ($color !== null) {
            $result = '<span style="color:' . $color . '">' . $result . '</span>';
        }

        return $result;
    }

    /**
     * Calculate information about HTML colspan and rowspan which is not always the same as Excel's.
     */
    private function calculateSpans(): void
    {
        if ($this->spansAreCalculated) {
            return;
        }
        // Identify all cells that should be omitted in HTML due to cell merge.
        // In HTML only the upper-left cell should be written and it should have
        //   appropriate rowspan / colspan attribute
<<<<<<< HEAD
        $sheetIndexes = $this->sheetIndex !== null
            ? [$this->sheetIndex] : range(0, $this->spreadsheet->getSheetCount() - 1);
=======
        $sheetIndexes = $this->sheetIndex !== null ?
            [$this->sheetIndex] : range(0, $this->spreadsheet->getSheetCount() - 1);
>>>>>>> main

        foreach ($sheetIndexes as $sheetIndex) {
            $sheet = $this->spreadsheet->getSheet($sheetIndex);

            $candidateSpannedRow = [];

            // loop through all Excel merged cells
            foreach ($sheet->getMergeCells() as $cells) {
                [$cells] = Coordinate::splitRange($cells);
                $first = $cells[0];
                $last = $cells[1];

                [$fc, $fr] = Coordinate::indexesFromString($first);
                $fc = $fc - 1;

                [$lc, $lr] = Coordinate::indexesFromString($last);
                $lc = $lc - 1;

                // loop through the individual cells in the individual merge
                $r = $fr - 1;
                while ($r++ < $lr) {
                    // also, flag this row as a HTML row that is candidate to be omitted
                    $candidateSpannedRow[$r] = $r;

                    $c = $fc - 1;
                    while ($c++ < $lc) {
                        if (!($c == $fc && $r == $fr)) {
                            // not the upper-left cell (should not be written in HTML)
                            $this->isSpannedCell[$sheetIndex][$r][$c] = [
                                'baseCell' => [$fr, $fc],
                            ];
                        } else {
                            // upper-left is the base cell that should hold the colspan/rowspan attribute
                            $this->isBaseCell[$sheetIndex][$r][$c] = [
                                'xlrowspan' => $lr - $fr + 1, // Excel rowspan
                                'rowspan' => $lr - $fr + 1, // HTML rowspan, value may change
                                'xlcolspan' => $lc - $fc + 1, // Excel colspan
                                'colspan' => $lc - $fc + 1, // HTML colspan, value may change
                            ];
                        }
                    }
                }
            }
<<<<<<< HEAD
=======

            $this->calculateSpansOmitRows($sheet, $sheetIndex, $candidateSpannedRow);

            // TODO: Same for columns
>>>>>>> main
        }

        // We have calculated the spans
        $this->spansAreCalculated = true;
    }

<<<<<<< HEAD
=======
    private function calculateSpansOmitRows(Worksheet $sheet, int $sheetIndex, array $candidateSpannedRow): void
    {
        // Identify which rows should be omitted in HTML. These are the rows where all the cells
        //   participate in a merge and the where base cells are somewhere above.
        $countColumns = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        foreach ($candidateSpannedRow as $rowIndex) {
            if (isset($this->isSpannedCell[$sheetIndex][$rowIndex])) {
                if (count($this->isSpannedCell[$sheetIndex][$rowIndex]) == $countColumns) {
                    $this->isSpannedRow[$sheetIndex][$rowIndex] = $rowIndex;
                }
            }
        }

        // For each of the omitted rows we found above, the affected rowspans should be subtracted by 1
        if (isset($this->isSpannedRow[$sheetIndex])) {
            foreach ($this->isSpannedRow[$sheetIndex] as $rowIndex) {
                $adjustedBaseCells = [];
                $c = -1;
                $e = $countColumns - 1;
                while ($c++ < $e) {
                    $baseCell = $this->isSpannedCell[$sheetIndex][$rowIndex][$c]['baseCell'];

                    if (!in_array($baseCell, $adjustedBaseCells, true)) {
                        // subtract rowspan by 1
                        --$this->isBaseCell[$sheetIndex][$baseCell[0]][$baseCell[1]]['rowspan'];
                        $adjustedBaseCells[] = $baseCell;
                    }
                }
            }
        }
    }

>>>>>>> main
    /**
     * Write a comment in the same format as LibreOffice.
     *
     * @see https://github.com/LibreOffice/core/blob/9fc9bf3240f8c62ad7859947ab8a033ac1fe93fa/sc/source/filter/html/htmlexp.cxx#L1073-L1092
<<<<<<< HEAD
     */
    private function writeComment(Worksheet $worksheet, string $coordinate): string
    {
        $result = '';
        if (!$this->isPdf && isset($worksheet->getComments()[$coordinate])) {
            $sanitizedString = $this->generateRowCellDataValueRich($worksheet->getComment($coordinate)->getText());
            $dir = ($worksheet->getComment($coordinate)->getTextboxDirection() === Comment::TEXTBOX_DIRECTION_RTL) ? ' dir="rtl"' : '';
            $align = strtolower($worksheet->getComment($coordinate)->getAlignment());
            $alignment = Alignment::HORIZONTAL_ALIGNMENT_FOR_HTML[$align] ?? '';
            if ($alignment !== '') {
                $alignment = " style=\"text-align:$alignment\"";
            }
            if ($sanitizedString !== '') {
                $result .= '<a class="comment-indicator"></a>';
                $result .= "<div class=\"comment\"$dir$alignment>" . $sanitizedString . '</div>';
                $result .= $this->lineEnding;
=======
     *
     * @param string $coordinate
     *
     * @return string
     */
    private function writeComment(Worksheet $worksheet, $coordinate)
    {
        $result = '';
        if (!$this->isPdf && isset($worksheet->getComments()[$coordinate])) {
            $sanitizer = new HTMLPurifier();
            $cachePath = File::sysGetTempDir() . '/phpsppur';
            if (is_dir($cachePath) || mkdir($cachePath)) {
                $sanitizer->config->set('Cache.SerializerPath', $cachePath);
            }
            $sanitizedString = $sanitizer->purify($worksheet->getComment($coordinate)->getText()->getPlainText());
            if ($sanitizedString !== '') {
                $result .= '<a class="comment-indicator"></a>';
                $result .= '<div class="comment">' . nl2br($sanitizedString) . '</div>';
                $result .= PHP_EOL;
>>>>>>> main
            }
        }

        return $result;
    }

    public function getOrientation(): ?string
    {
        // Expect Pdf classes to override this method.
        return $this->isPdf ? PageSetup::ORIENTATION_PORTRAIT : null;
    }

    /**
     * Generate @page declarations.
<<<<<<< HEAD
     */
    private function generatePageDeclarations(bool $generateSurroundingHTML): string
=======
     *
     * @param bool $generateSurroundingHTML
     *
     * @return    string
     */
    private function generatePageDeclarations($generateSurroundingHTML)
>>>>>>> main
    {
        // Ensure that Spans have been calculated?
        $this->calculateSpans();

        // Fetch sheets
        $sheets = [];
        if ($this->sheetIndex === null) {
            $sheets = $this->spreadsheet->getAllSheets();
        } else {
            $sheets[] = $this->spreadsheet->getSheet($this->sheetIndex);
        }

        // Construct HTML
<<<<<<< HEAD
        $htmlPage = $generateSurroundingHTML ? ('<style type="text/css">' . $this->lineEnding) : '';
=======
        $htmlPage = $generateSurroundingHTML ? ('<style type="text/css">' . PHP_EOL) : '';
>>>>>>> main

        // Loop all sheets
        $sheetId = 0;
        foreach ($sheets as $worksheet) {
            $htmlPage .= "@page page$sheetId { ";
            $left = StringHelper::formatNumber($worksheet->getPageMargins()->getLeft()) . 'in; ';
            $htmlPage .= 'margin-left: ' . $left;
            $right = StringHelper::FormatNumber($worksheet->getPageMargins()->getRight()) . 'in; ';
            $htmlPage .= 'margin-right: ' . $right;
            $top = StringHelper::FormatNumber($worksheet->getPageMargins()->getTop()) . 'in; ';
            $htmlPage .= 'margin-top: ' . $top;
            $bottom = StringHelper::FormatNumber($worksheet->getPageMargins()->getBottom()) . 'in; ';
            $htmlPage .= 'margin-bottom: ' . $bottom;
            $orientation = $this->getOrientation() ?? $worksheet->getPageSetup()->getOrientation();
            if ($orientation === PageSetup::ORIENTATION_LANDSCAPE) {
                $htmlPage .= 'size: landscape; ';
            } elseif ($orientation === PageSetup::ORIENTATION_PORTRAIT) {
                $htmlPage .= 'size: portrait; ';
            }
<<<<<<< HEAD
            $htmlPage .= '}' . $this->lineEnding;
            if (!$this->isPdf) {
                $htmlPage .= $this->printAreaStyles($sheetId, $worksheet);
            }
            ++$sheetId;
        }
        $htmlPage .= implode($this->lineEnding, [
=======
            $htmlPage .= '}' . PHP_EOL;
            ++$sheetId;
        }
        $htmlPage .= implode(PHP_EOL, [
>>>>>>> main
            '.navigation {page-break-after: always;}',
            '.scrpgbrk, div + div {page-break-before: always;}',
            '@media screen {',
            '  .gridlines td {border: 1px solid black;}',
            '  .gridlines th {border: 1px solid black;}',
            '  body>div {margin-top: 5px;}',
            '  body>div:first-child {margin-top: 0;}',
            '  .scrpgbrk {margin-top: 1px;}',
            '}',
            '@media print {',
            '  .gridlinesp td {border: 1px solid black;}',
            '  .gridlinesp th {border: 1px solid black;}',
            '  .navigation {display: none;}',
            '}',
            '',
        ]);
<<<<<<< HEAD
        $htmlPage .= $generateSurroundingHTML ? ('</style>' . $this->lineEnding) : '';

        return $htmlPage;
    }

    private function printAreaStyles(int $sheetId, Worksheet $worksheet): string
    {
        $retVal = '';
        $printArea = $worksheet->getPageSetup()->getPrintArea();
        if (Preg::isMatch('/^([a-z]+)([0-9]+):([a-z]+)([0-9]+)$/i', $printArea, $matches)) {
            $lowCol = Coordinate::columnIndexFromString($matches[1]) - 1;
            $highCol = Coordinate::columnIndexFromString($matches[3]) - 1;
            $lowRow = (int) $matches[2] - 1;
            $highRow = (int) $matches[4] - 1;
            $retVal = '@media print {' . $this->lineEnding;
            $highDataRow = $worksheet->getHighestDataRow();
            for ($row = 0; $row < $highDataRow; ++$row) {
                if ($row < $lowRow || $row > $highRow) {
                    $retVal .= "    table.sheet$sheetId tr.row$row td { display:none }" . $this->lineEnding;
                }
            }
            $highDataColumn = $worksheet->getHighestDataColumn();
            $highDataCol = Coordinate::columnIndexFromString($highDataColumn);
            for ($col = 0; $col < $highDataCol; ++$col) {
                if ($col < $lowCol || $col > $highCol) {
                    $retVal .= "    table.sheet$sheetId td.column$col { display:none }" . $this->lineEnding;
                }
            }
            $retVal .= '}' . $this->lineEnding;
        }

        return $retVal;
    }

    private function shouldGenerateRow(Worksheet $sheet, int $row): bool
    {
        if ($this->isPdf) {
            if ($this->printAreaLowRow >= 0) {
                if ($row < $this->printAreaLowRow || $row > $this->printAreaHighRow) {
                    return false;
                }
            }
        }
        if (!($this instanceof Pdf\Mpdf || $this instanceof Pdf\Tcpdf)) {
            return true;
        }

        return $sheet->isRowVisible($row);
    }

    private function shouldGenerateColumn(Worksheet $sheet, string $colStr): bool
    {
        if ($this->isPdf) {
            if ($this->printAreaLowCol >= 0) {
                $col = Coordinate::columnIndexFromString($colStr);
                if ($col < $this->printAreaLowCol || $col > $this->printAreaHighCol) {
                    return false;
                }
            }
        }
        if (!($this instanceof Pdf\Mpdf || $this instanceof Pdf\Tcpdf)) {
            return true;
        }
        if (!$sheet->columnDimensionExists($colStr)) {
            return true;
        }

        return $sheet->getColumnDimension($colStr)->getVisible();
    }

    public function getBetterBoolean(): bool
    {
        return $this->betterBoolean;
    }

    public function setBetterBoolean(bool $betterBoolean): self
    {
        $this->betterBoolean = $betterBoolean;

        return $this;
    }

    private static function nl2brx(string $string, bool $useXhtml = false): string
    {
        return str_replace(
            ["\r\n", "\n\r", "\r", "\n"],
            self::BRX,
            $string
        );
    }

    private function extendRowsAndColumnsForMerge(Worksheet $worksheet, int &$colMax, int &$rowMax): void
    {
        foreach ($worksheet->getMergeCells() as $cellRange) {
            if (Preg::isMatch('/[a-z]{1,3}\d+:([a-z]{1,3})(\d+)/i', $cellRange, $matches)) {
                $col = Coordinate::columnIndexFromString($matches[1]);
                if ($colMax < $col) {
                    $colMax = $col;
                    $worksheet->getColumnDimension($matches[1]);
                }
                $row = (int) $matches[2];
                if ($rowMax < $row) {
                    $rowMax = $row;
                    $worksheet->getRowDimension($row);
                }
            }
        }
    }
=======
        $htmlPage .= $generateSurroundingHTML ? ('</style>' . PHP_EOL) : '';

        return $htmlPage;
    }
>>>>>>> main
}
