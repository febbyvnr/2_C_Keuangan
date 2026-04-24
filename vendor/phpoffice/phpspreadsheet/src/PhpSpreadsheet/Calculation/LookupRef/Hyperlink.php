<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\LookupRef;

use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
=======
>>>>>>> main

class Hyperlink
{
    /**
     * HYPERLINK.
     *
     * Excel Function:
     *        =HYPERLINK(linkURL, [displayName])
     *
     * @param mixed $linkURL Expect string. Value to check, is also the value returned when no error
     * @param mixed $displayName Expect string. Value to return when testValue is an error condition
<<<<<<< HEAD
     * @param ?Cell $cell The cell to set the hyperlink in
     *
     * @return string The value of $displayName (or $linkURL if $displayName was blank)
     */
    public static function set(mixed $linkURL = '', mixed $displayName = null, ?Cell $cell = null): string
    {
        $worksheet = null;
        $coordinate = '';
        if ($cell !== null) {
            $coordinate = $cell->getCoordinate();
            $worksheet = $cell->getWorksheetOrNull();
        }

        $linkURL = ($linkURL === null) ? '' : StringHelper::convertToString(Functions::flattenSingleValue($linkURL));
=======
     * @param Cell $cell The cell to set the hyperlink in
     *
     * @return mixed The value of $displayName (or $linkURL if $displayName was blank)
     */
    public static function set($linkURL = '', $displayName = null, ?Cell $cell = null)
    {
        $linkURL = ($linkURL === null) ? '' : Functions::flattenSingleValue($linkURL);
>>>>>>> main
        $displayName = ($displayName === null) ? '' : Functions::flattenSingleValue($displayName);

        if ((!is_object($cell)) || (trim($linkURL) == '')) {
            return ExcelError::REF();
        }

<<<<<<< HEAD
        $displayName = StringHelper::convertToString($displayName, false);
        if (trim($displayName) === '') {
            $displayName = $linkURL;
        }

        $worksheet?->getCell($coordinate)
            ->getHyperlink()
            ->setUrl($linkURL)
            ->setTooltip($displayName)
            ->setDisplay('');
=======
        if ((is_object($displayName)) || trim($displayName) == '') {
            $displayName = $linkURL;
        }

        $cell->getHyperlink()->setUrl($linkURL);
        $cell->getHyperlink()->setTooltip($displayName);
>>>>>>> main

        return $displayName;
    }
}
