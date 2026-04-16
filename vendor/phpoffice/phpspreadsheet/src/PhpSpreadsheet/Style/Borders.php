<?php

namespace PhpOffice\PhpSpreadsheet\Style;

use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;

class Borders extends Supervisor
{
    // Diagonal directions
    const DIAGONAL_NONE = 0;
    const DIAGONAL_UP = 1;
    const DIAGONAL_DOWN = 2;
    const DIAGONAL_BOTH = 3;

    /**
     * Left.
<<<<<<< HEAD
     */
    protected Border $left;

    /**
     * Right.
     */
    protected Border $right;

    /**
     * Top.
     */
    protected Border $top;

    /**
     * Bottom.
     */
    protected Border $bottom;

    /**
     * Diagonal.
     */
    protected Border $diagonal;

    /**
     * DiagonalDirection.
     */
    protected int $diagonalDirection;

    /**
     * All borders pseudo-border. Only applies to supervisor.
     */
    protected Border $allBorders;

    /**
     * Outline pseudo-border. Only applies to supervisor.
     */
    protected Border $outline;

    /**
     * Inside pseudo-border. Only applies to supervisor.
     */
    protected Border $inside;

    /**
     * Vertical pseudo-border. Only applies to supervisor.
     */
    protected Border $vertical;

    /**
     * Horizontal pseudo-border. Only applies to supervisor.
     */
    protected Border $horizontal;
=======
     *
     * @var Border
     */
    protected $left;

    /**
     * Right.
     *
     * @var Border
     */
    protected $right;

    /**
     * Top.
     *
     * @var Border
     */
    protected $top;

    /**
     * Bottom.
     *
     * @var Border
     */
    protected $bottom;

    /**
     * Diagonal.
     *
     * @var Border
     */
    protected $diagonal;

    /**
     * DiagonalDirection.
     *
     * @var int
     */
    protected $diagonalDirection;

    /**
     * All borders pseudo-border. Only applies to supervisor.
     *
     * @var Border
     */
    protected $allBorders;

    /**
     * Outline pseudo-border. Only applies to supervisor.
     *
     * @var Border
     */
    protected $outline;

    /**
     * Inside pseudo-border. Only applies to supervisor.
     *
     * @var Border
     */
    protected $inside;

    /**
     * Vertical pseudo-border. Only applies to supervisor.
     *
     * @var Border
     */
    protected $vertical;

    /**
     * Horizontal pseudo-border. Only applies to supervisor.
     *
     * @var Border
     */
    protected $horizontal;
>>>>>>> main

    /**
     * Create a new Borders.
     *
     * @param bool $isSupervisor Flag indicating if this is a supervisor or not
     *                                    Leave this value at default unless you understand exactly what
     *                                        its ramifications are
     */
<<<<<<< HEAD
    public function __construct(bool $isSupervisor = false, bool $isConditional = false)
=======
    public function __construct($isSupervisor = false, bool $isConditional = false)
>>>>>>> main
    {
        // Supervisor?
        parent::__construct($isSupervisor);

        // Initialise values
        $this->left = new Border($isSupervisor, $isConditional);
        $this->right = new Border($isSupervisor, $isConditional);
        $this->top = new Border($isSupervisor, $isConditional);
        $this->bottom = new Border($isSupervisor, $isConditional);
        $this->diagonal = new Border($isSupervisor, $isConditional);
        $this->diagonalDirection = self::DIAGONAL_NONE;

        // Specially for supervisor
        if ($isSupervisor) {
            // Initialize pseudo-borders
            $this->allBorders = new Border(true, $isConditional);
            $this->outline = new Border(true, $isConditional);
            $this->inside = new Border(true, $isConditional);
            $this->vertical = new Border(true, $isConditional);
            $this->horizontal = new Border(true, $isConditional);

            // bind parent if we are a supervisor
            $this->left->bindParent($this, 'left');
            $this->right->bindParent($this, 'right');
            $this->top->bindParent($this, 'top');
            $this->bottom->bindParent($this, 'bottom');
            $this->diagonal->bindParent($this, 'diagonal');
            $this->allBorders->bindParent($this, 'allBorders');
            $this->outline->bindParent($this, 'outline');
            $this->inside->bindParent($this, 'inside');
            $this->vertical->bindParent($this, 'vertical');
            $this->horizontal->bindParent($this, 'horizontal');
        }
    }

    /**
     * Get the shared style component for the currently active cell in currently active sheet.
     * Only used for style supervisor.
<<<<<<< HEAD
     */
    public function getSharedComponent(): self
    {
        /** @var Style $parent */
=======
     *
     * @return Borders
     */
    public function getSharedComponent()
    {
        /** @var Style */
>>>>>>> main
        $parent = $this->parent;

        return $parent->getSharedComponent()->getBorders();
    }

    /**
     * Build style array from subcomponents.
     *
<<<<<<< HEAD
     * @param mixed[] $array
     *
     * @return array{borders: mixed[]}
     */
    public function getStyleArray(array $array): array
=======
     * @param array $array
     *
     * @return array
     */
    public function getStyleArray($array)
>>>>>>> main
    {
        return ['borders' => $array];
    }

    /**
     * Apply styles from array.
     *
     * <code>
     * $spreadsheet->getActiveSheet()->getStyle('B2')->getBorders()->applyFromArray(
     *         [
     *             'bottom' => [
     *                 'borderStyle' => Border::BORDER_DASHDOT,
     *                 'color' => [
     *                     'rgb' => '808080'
     *                 ]
     *             ],
     *             'top' => [
     *                 'borderStyle' => Border::BORDER_DASHDOT,
     *                 'color' => [
     *                     'rgb' => '808080'
     *                 ]
     *             ]
     *         ]
     * );
     * </code>
     *
     * <code>
     * $spreadsheet->getActiveSheet()->getStyle('B2')->getBorders()->applyFromArray(
     *         [
     *             'allBorders' => [
     *                 'borderStyle' => Border::BORDER_DASHDOT,
     *                 'color' => [
     *                     'rgb' => '808080'
     *                 ]
     *             ]
     *         ]
     * );
     * </code>
     *
<<<<<<< HEAD
     * @param mixed[] $styleArray Array containing style information
     *
     * @return $this
     */
    public function applyFromArray(array $styleArray): static
=======
     * @param array $styleArray Array containing style information
     *
     * @return $this
     */
    public function applyFromArray(array $styleArray)
>>>>>>> main
    {
        if ($this->isSupervisor) {
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($this->getStyleArray($styleArray));
        } else {
<<<<<<< HEAD
            /** @var array{left?: float[], right?: float[], top?: float[], bottom?: float[], diagonal?: mixed[], diagonalDirection?: int, allBorders?: mixed[][]} $styleArray */
=======
>>>>>>> main
            if (isset($styleArray['left'])) {
                $this->getLeft()->applyFromArray($styleArray['left']);
            }
            if (isset($styleArray['right'])) {
                $this->getRight()->applyFromArray($styleArray['right']);
            }
            if (isset($styleArray['top'])) {
                $this->getTop()->applyFromArray($styleArray['top']);
            }
            if (isset($styleArray['bottom'])) {
                $this->getBottom()->applyFromArray($styleArray['bottom']);
            }
            if (isset($styleArray['diagonal'])) {
                $this->getDiagonal()->applyFromArray($styleArray['diagonal']);
            }
            if (isset($styleArray['diagonalDirection'])) {
                $this->setDiagonalDirection($styleArray['diagonalDirection']);
            }
            if (isset($styleArray['allBorders'])) {
                $this->getLeft()->applyFromArray($styleArray['allBorders']);
                $this->getRight()->applyFromArray($styleArray['allBorders']);
                $this->getTop()->applyFromArray($styleArray['allBorders']);
                $this->getBottom()->applyFromArray($styleArray['allBorders']);
            }
        }

        return $this;
    }

    /**
     * Get Left.
<<<<<<< HEAD
     */
    public function getLeft(): Border
=======
     *
     * @return Border
     */
    public function getLeft()
>>>>>>> main
    {
        return $this->left;
    }

    /**
     * Get Right.
<<<<<<< HEAD
     */
    public function getRight(): Border
=======
     *
     * @return Border
     */
    public function getRight()
>>>>>>> main
    {
        return $this->right;
    }

    /**
     * Get Top.
<<<<<<< HEAD
     */
    public function getTop(): Border
=======
     *
     * @return Border
     */
    public function getTop()
>>>>>>> main
    {
        return $this->top;
    }

    /**
     * Get Bottom.
<<<<<<< HEAD
     */
    public function getBottom(): Border
=======
     *
     * @return Border
     */
    public function getBottom()
>>>>>>> main
    {
        return $this->bottom;
    }

    /**
     * Get Diagonal.
<<<<<<< HEAD
     */
    public function getDiagonal(): Border
=======
     *
     * @return Border
     */
    public function getDiagonal()
>>>>>>> main
    {
        return $this->diagonal;
    }

    /**
     * Get AllBorders (pseudo-border). Only applies to supervisor.
<<<<<<< HEAD
     */
    public function getAllBorders(): Border
=======
     *
     * @return Border
     */
    public function getAllBorders()
>>>>>>> main
    {
        if (!$this->isSupervisor) {
            throw new PhpSpreadsheetException('Can only get pseudo-border for supervisor.');
        }

        return $this->allBorders;
    }

    /**
     * Get Outline (pseudo-border). Only applies to supervisor.
<<<<<<< HEAD
     */
    public function getOutline(): Border
=======
     *
     * @return Border
     */
    public function getOutline()
>>>>>>> main
    {
        if (!$this->isSupervisor) {
            throw new PhpSpreadsheetException('Can only get pseudo-border for supervisor.');
        }

        return $this->outline;
    }

    /**
     * Get Inside (pseudo-border). Only applies to supervisor.
<<<<<<< HEAD
     */
    public function getInside(): Border
=======
     *
     * @return Border
     */
    public function getInside()
>>>>>>> main
    {
        if (!$this->isSupervisor) {
            throw new PhpSpreadsheetException('Can only get pseudo-border for supervisor.');
        }

        return $this->inside;
    }

    /**
     * Get Vertical (pseudo-border). Only applies to supervisor.
<<<<<<< HEAD
     */
    public function getVertical(): Border
=======
     *
     * @return Border
     */
    public function getVertical()
>>>>>>> main
    {
        if (!$this->isSupervisor) {
            throw new PhpSpreadsheetException('Can only get pseudo-border for supervisor.');
        }

        return $this->vertical;
    }

    /**
     * Get Horizontal (pseudo-border). Only applies to supervisor.
<<<<<<< HEAD
     */
    public function getHorizontal(): Border
=======
     *
     * @return Border
     */
    public function getHorizontal()
>>>>>>> main
    {
        if (!$this->isSupervisor) {
            throw new PhpSpreadsheetException('Can only get pseudo-border for supervisor.');
        }

        return $this->horizontal;
    }

    /**
     * Get DiagonalDirection.
<<<<<<< HEAD
     */
    public function getDiagonalDirection(): int
=======
     *
     * @return int
     */
    public function getDiagonalDirection()
>>>>>>> main
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getDiagonalDirection();
        }

        return $this->diagonalDirection;
    }

    /**
     * Set DiagonalDirection.
     *
     * @param int $direction see self::DIAGONAL_*
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setDiagonalDirection(int $direction): static
    {
=======
    public function setDiagonalDirection($direction)
    {
        if ($direction == '') {
            $direction = self::DIAGONAL_NONE;
        }
>>>>>>> main
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(['diagonalDirection' => $direction]);
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->diagonalDirection = $direction;
        }

        return $this;
    }

    /**
     * Get hash code.
     *
     * @return string Hash code
     */
<<<<<<< HEAD
    public function getHashCode(): string
=======
    public function getHashCode()
>>>>>>> main
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getHashcode();
        }

        return md5(
<<<<<<< HEAD
            $this->getLeft()->getHashCode()
            . $this->getRight()->getHashCode()
            . $this->getTop()->getHashCode()
            . $this->getBottom()->getHashCode()
            . $this->getDiagonal()->getHashCode()
            . $this->getDiagonalDirection()
            . __CLASS__
        );
    }

    /** @return mixed[][] */
=======
            $this->getLeft()->getHashCode() .
            $this->getRight()->getHashCode() .
            $this->getTop()->getHashCode() .
            $this->getBottom()->getHashCode() .
            $this->getDiagonal()->getHashCode() .
            $this->getDiagonalDirection() .
            __CLASS__
        );
    }

>>>>>>> main
    protected function exportArray1(): array
    {
        $exportedArray = [];
        $this->exportArray2($exportedArray, 'bottom', $this->getBottom());
        $this->exportArray2($exportedArray, 'diagonal', $this->getDiagonal());
        $this->exportArray2($exportedArray, 'diagonalDirection', $this->getDiagonalDirection());
        $this->exportArray2($exportedArray, 'left', $this->getLeft());
        $this->exportArray2($exportedArray, 'right', $this->getRight());
        $this->exportArray2($exportedArray, 'top', $this->getTop());
<<<<<<< HEAD
        /** @var mixed[][] $exportedArray */
=======
>>>>>>> main

        return $exportedArray;
    }
}
