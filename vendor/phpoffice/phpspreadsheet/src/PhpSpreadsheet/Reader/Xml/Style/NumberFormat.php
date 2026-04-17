<?php

namespace PhpOffice\PhpSpreadsheet\Reader\Xml\Style;

use SimpleXMLElement;

class NumberFormat extends StyleBase
{
<<<<<<< HEAD
    /** @return mixed[] */
=======
>>>>>>> main
    public function parseStyle(SimpleXMLElement $styleAttributes): array
    {
        $style = [];

        $fromFormats = ['\-', '\ '];
        $toFormats = ['-', ' '];

        foreach ($styleAttributes as $styleAttributeKey => $styleAttributeValue) {
<<<<<<< HEAD
            $styleAttributeValue = str_replace($fromFormats, $toFormats, (string) $styleAttributeValue);
=======
            $styleAttributeValue = str_replace($fromFormats, $toFormats, $styleAttributeValue);
>>>>>>> main

            switch ($styleAttributeValue) {
                case 'Short Date':
                    $styleAttributeValue = 'dd/mm/yyyy';

                    break;
            }

            if ($styleAttributeValue > '') {
                $style['numberFormat']['formatCode'] = $styleAttributeValue;
            }
        }

        return $style;
    }
}
