<?php

namespace PhpOffice\PhpSpreadsheet\Cell;

interface IValueBinder
{
    /**
     * Bind value to a cell.
     *
     * @param Cell $cell Cell to bind value to
     * @param mixed $value Value to bind in cell
<<<<<<< HEAD
     */
    public function bindValue(Cell $cell, mixed $value): bool;
=======
     *
     * @return bool
     */
    public function bindValue(Cell $cell, $value);
>>>>>>> main
}
