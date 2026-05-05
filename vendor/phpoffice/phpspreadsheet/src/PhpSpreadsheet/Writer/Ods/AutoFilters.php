<?php

namespace PhpOffice\PhpSpreadsheet\Writer\Ods;

use PhpOffice\PhpSpreadsheet\Shared\XMLWriter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AutoFilters
{
<<<<<<< HEAD
    private XMLWriter $objWriter;

    private Spreadsheet $spreadsheet;
=======
    /**
     * @var XMLWriter
     */
    private $objWriter;

    /**
     * @var Spreadsheet
     */
    private $spreadsheet;
>>>>>>> main

    public function __construct(XMLWriter $objWriter, Spreadsheet $spreadsheet)
    {
        $this->objWriter = $objWriter;
        $this->spreadsheet = $spreadsheet;
    }

<<<<<<< HEAD
    public function write(): void
    {
        $wrapperWritten = false;
=======
    /** @var mixed */
    private static $scrutinizerFalse = false;

    public function write(): void
    {
        $wrapperWritten = self::$scrutinizerFalse;
>>>>>>> main
        $sheetCount = $this->spreadsheet->getSheetCount();
        for ($i = 0; $i < $sheetCount; ++$i) {
            $worksheet = $this->spreadsheet->getSheet($i);
            $autofilter = $worksheet->getAutoFilter();
<<<<<<< HEAD
            if (!empty($autofilter->getRange())) {
=======
            if ($autofilter !== null && !empty($autofilter->getRange())) {
>>>>>>> main
                if ($wrapperWritten === false) {
                    $this->objWriter->startElement('table:database-ranges');
                    $wrapperWritten = true;
                }
                $this->objWriter->startElement('table:database-range');
                $this->objWriter->writeAttribute('table:orientation', 'column');
                $this->objWriter->writeAttribute('table:display-filter-buttons', 'true');
                $this->objWriter->writeAttribute(
                    'table:target-range-address',
                    $this->formatRange($worksheet, $autofilter)
                );
                $this->objWriter->endElement();
            }
        }

        if ($wrapperWritten === true) {
            $this->objWriter->endElement();
        }
    }

<<<<<<< HEAD
    protected function formatRange(Worksheet $worksheet, AutoFilter $autofilter): string
=======
    protected function formatRange(Worksheet $worksheet, Autofilter $autofilter): string
>>>>>>> main
    {
        $title = $worksheet->getTitle();
        $range = $autofilter->getRange();

        return "'{$title}'.{$range}";
    }
}
