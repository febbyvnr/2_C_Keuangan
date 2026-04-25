<?php

namespace PhpOffice\PhpSpreadsheet\Worksheet;

use PhpOffice\PhpSpreadsheet\Helper\Dimension as CssDimension;

class RowDimension extends Dimension
{
<<<<<<< HEAD
    private ?int $rowIndex;
=======
    /**
     * Row index.
     *
     * @var ?int
     */
    private $rowIndex;
>>>>>>> main

    /**
     * Row height (in pt).
     *
     * When this is set to a negative value, the row height should be ignored by IWriter
<<<<<<< HEAD
     */
    private float $height = -1;

    /**
     * ZeroHeight for Row?
     */
    private bool $zeroHeight = false;

    private bool $customFormat = false;

    private bool $visibleAfterFilter = true;

    public function setVisibleAfterFilter(bool $visibleAfterFilter): self
    {
        $this->visibleAfterFilter = $visibleAfterFilter;

        return $this;
    }

    public function getVisibleAfterFilter(): bool
    {
        return $this->visibleAfterFilter;
    }

    /**
     * @param ?int $index Numeric row index
     */
    public function __construct(?int $index = 0)
=======
     *
     * @var float
     */
    private $height = -1;

    /**
     * ZeroHeight for Row?
     *
     * @var bool
     */
    private $zeroHeight = false;

    /**
     * Create a new RowDimension.
     *
     * @param ?int $index Numeric row index
     */
    public function __construct($index = 0)
>>>>>>> main
    {
        // Initialise values
        $this->rowIndex = $index;

        // set dimension as unformatted by default
        parent::__construct(null);
    }

<<<<<<< HEAD
=======
    /**
     * Get Row Index.
     */
>>>>>>> main
    public function getRowIndex(): ?int
    {
        return $this->rowIndex;
    }

<<<<<<< HEAD
    public function setRowIndex(int $index): static
=======
    /**
     * Set Row Index.
     *
     * @return $this
     */
    public function setRowIndex(int $index)
>>>>>>> main
    {
        $this->rowIndex = $index;

        return $this;
    }

    /**
     * Get Row Height.
     * By default, this will be in points; but this method also accepts an optional unit of measure
     *    argument, and will convert the value from points to the specified UoM.
     *    A value of -1 tells Excel to display this column in its default height.
<<<<<<< HEAD
     */
    public function getRowHeight(?string $unitOfMeasure = null): float
=======
     *
     * @return float
     */
    public function getRowHeight(?string $unitOfMeasure = null)
>>>>>>> main
    {
        return ($unitOfMeasure === null || $this->height < 0)
            ? $this->height
            : (new CssDimension($this->height . CssDimension::UOM_POINTS))->toUnit($unitOfMeasure);
    }

    /**
     * Set Row Height.
     *
     * @param float $height in points. A value of -1 tells Excel to display this column in its default height.
     * By default, this will be the passed argument value; but this method also accepts an optional unit of measure
     *    argument, and will convert the passed argument value to points from the specified UoM
<<<<<<< HEAD
     */
    public function setRowHeight(float $height, ?string $unitOfMeasure = null): static
=======
     *
     * @return $this
     */
    public function setRowHeight($height, ?string $unitOfMeasure = null)
>>>>>>> main
    {
        $this->height = ($unitOfMeasure === null || $height < 0)
            ? $height
            : (new CssDimension("{$height}{$unitOfMeasure}"))->height();
<<<<<<< HEAD
        $this->customFormat = false;
=======
>>>>>>> main

        return $this;
    }

<<<<<<< HEAD
=======
    /**
     * Get ZeroHeight.
     */
>>>>>>> main
    public function getZeroHeight(): bool
    {
        return $this->zeroHeight;
    }

<<<<<<< HEAD
    public function setZeroHeight(bool $zeroHeight): static
=======
    /**
     * Set ZeroHeight.
     *
     * @return $this
     */
    public function setZeroHeight(bool $zeroHeight)
>>>>>>> main
    {
        $this->zeroHeight = $zeroHeight;

        return $this;
    }
<<<<<<< HEAD

    public function getCustomFormat(): bool
    {
        return $this->customFormat;
    }

    public function setCustomFormat(bool $customFormat, ?float $height = -1): self
    {
        $this->customFormat = $customFormat;
        if ($height !== null) {
            $this->height = $height;
        }

        return $this;
    }
=======
>>>>>>> main
}
