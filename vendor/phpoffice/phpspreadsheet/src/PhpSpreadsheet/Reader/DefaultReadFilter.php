<?php

namespace PhpOffice\PhpSpreadsheet\Reader;

class DefaultReadFilter implements IReadFilter
{
    /**
     * Should this cell be read?
     *
     * @param string $columnAddress Column address (as a string value like "A", or "IV")
     * @param int $row Row number
     * @param string $worksheetName Optional worksheet name
<<<<<<< HEAD
     */
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
=======
     *
     * @return bool
     */
    public function readCell($columnAddress, $row, $worksheetName = '')
>>>>>>> main
    {
        return true;
    }
}
