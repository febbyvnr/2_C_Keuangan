<?php

namespace PhpOffice\PhpSpreadsheet\Style;

use PhpOffice\PhpSpreadsheet\IComparable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class Supervisor implements IComparable
{
    /**
     * Supervisor?
<<<<<<< HEAD
     */
    protected bool $isSupervisor;
=======
     *
     * @var bool
     */
    protected $isSupervisor;
>>>>>>> main

    /**
     * Parent. Only used for supervisor.
     *
     * @var Spreadsheet|Supervisor
     */
    protected $parent;

    /**
     * Parent property name.
<<<<<<< HEAD
     */
    protected ?string $parentPropertyName = null;
=======
     *
     * @var null|string
     */
    protected $parentPropertyName;
>>>>>>> main

    /**
     * Create a new Supervisor.
     *
     * @param bool $isSupervisor Flag indicating if this is a supervisor or not
     *                                    Leave this value at default unless you understand exactly what
     *                                        its ramifications are
     */
<<<<<<< HEAD
    public function __construct(bool $isSupervisor = false)
=======
    public function __construct($isSupervisor = false)
>>>>>>> main
    {
        // Supervisor?
        $this->isSupervisor = $isSupervisor;
    }

    /**
     * Bind parent. Only used for supervisor.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function bindParent(Spreadsheet|self $parent, ?string $parentPropertyName = null)
=======
     * @param Spreadsheet|Supervisor $parent
     * @param null|string $parentPropertyName
     *
     * @return $this
     */
    public function bindParent($parent, $parentPropertyName = null)
>>>>>>> main
    {
        $this->parent = $parent;
        $this->parentPropertyName = $parentPropertyName;

        return $this;
    }

    /**
     * Is this a supervisor or a cell style component?
<<<<<<< HEAD
     */
    public function getIsSupervisor(): bool
=======
     *
     * @return bool
     */
    public function getIsSupervisor()
>>>>>>> main
    {
        return $this->isSupervisor;
    }

    /**
     * Get the currently active sheet. Only used for supervisor.
<<<<<<< HEAD
     */
    public function getActiveSheet(): Worksheet
=======
     *
     * @return Worksheet
     */
    public function getActiveSheet()
>>>>>>> main
    {
        return $this->parent->getActiveSheet();
    }

    /**
     * Get the currently active cell coordinate in currently active sheet.
     * Only used for supervisor.
     *
     * @return string E.g. 'A1'
     */
<<<<<<< HEAD
    public function getSelectedCells(): string
=======
    public function getSelectedCells()
>>>>>>> main
    {
        return $this->getActiveSheet()->getSelectedCells();
    }

    /**
     * Get the currently active cell coordinate in currently active sheet.
     * Only used for supervisor.
     *
     * @return string E.g. 'A1'
     */
<<<<<<< HEAD
    public function getActiveCell(): string
=======
    public function getActiveCell()
>>>>>>> main
    {
        return $this->getActiveSheet()->getActiveCell();
    }

    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     */
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if ((is_object($value)) && ($key != 'parent')) {
                $this->$key = clone $value;
            } else {
                $this->$key = $value;
            }
        }
    }

    /**
     * Export style as array.
     *
     * Available to anything which extends this class:
     * Alignment, Border, Borders, Color, Fill, Font,
     * NumberFormat, Protection, and Style.
<<<<<<< HEAD
     *
     * @return mixed[]
=======
>>>>>>> main
     */
    final public function exportArray(): array
    {
        return $this->exportArray1();
    }

    /**
     * Abstract method to be implemented in anything which
     * extends this class.
     *
     * This method invokes exportArray2 with the names and values
     * of all properties to be included in output array,
     * returning that array to exportArray, then to caller.
<<<<<<< HEAD
     *
     * @return mixed[]
=======
>>>>>>> main
     */
    abstract protected function exportArray1(): array;

    /**
     * Populate array from exportArray1.
     * This method is available to anything which extends this class.
     * The parameter index is the key to be added to the array.
     * The parameter objOrValue is either a primitive type,
     * which is the value added to the array,
     * or a Style object to be recursively added via exportArray.
     *
<<<<<<< HEAD
     * @param mixed[] $exportedArray
     */
    final protected function exportArray2(array &$exportedArray, string $index, mixed $objOrValue): void
=======
     * @param mixed $objOrValue
     */
    final protected function exportArray2(array &$exportedArray, string $index, $objOrValue): void
>>>>>>> main
    {
        if ($objOrValue instanceof self) {
            $exportedArray[$index] = $objOrValue->exportArray();
        } else {
            $exportedArray[$index] = $objOrValue;
        }
    }

    /**
     * Get the shared style component for the currently active cell in currently active sheet.
     * Only used for style supervisor.
<<<<<<< HEAD
     */
    abstract public function getSharedComponent(): mixed;
=======
     *
     * @return mixed
     */
    abstract public function getSharedComponent();
>>>>>>> main

    /**
     * Build style array from subcomponents.
     *
<<<<<<< HEAD
     * @param mixed[] $array
     *
     * @return mixed[]
     */
    abstract public function getStyleArray(array $array): array;
=======
     * @param array $array
     *
     * @return array
     */
    abstract public function getStyleArray($array);
>>>>>>> main
}
