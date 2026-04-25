<?php

namespace PhpOffice\PhpSpreadsheet\Shared\Escher\DgContainer\SpgrContainer;

use PhpOffice\PhpSpreadsheet\Shared\Escher\DgContainer\SpgrContainer;

class SpContainer
{
    /**
     * Parent Shape Group Container.
<<<<<<< HEAD
     */
    private SpgrContainer $parent;

    /**
     * Is this a group shape?
     */
    private bool $spgr = false;

    /**
     * Shape type.
     */
    private int $spType;

    /**
     * Shape flag.
     */
    private int $spFlag;

    /**
     * Shape index (usually group shape has index 0, and the rest: 1,2,3...).
     */
    private int $spId;
=======
     *
     * @var SpgrContainer
     */
    private $parent;

    /**
     * Is this a group shape?
     *
     * @var bool
     */
    private $spgr = false;

    /**
     * Shape type.
     *
     * @var int
     */
    private $spType;

    /**
     * Shape flag.
     *
     * @var int
     */
    private $spFlag;

    /**
     * Shape index (usually group shape has index 0, and the rest: 1,2,3...).
     *
     * @var int
     */
    private $spId;
>>>>>>> main

    /**
     * Array of options.
     *
<<<<<<< HEAD
     * @var mixed[]
     */
    private array $OPT = [];

    /**
     * Cell coordinates of upper-left corner of shape, e.g. 'A1'.
     */
    private string $startCoordinates = '';

    /**
     * Horizontal offset of upper-left corner of shape measured in 1/1024 of column width.
     */
    private int|float $startOffsetX;

    /**
     * Vertical offset of upper-left corner of shape measured in 1/256 of row height.
     */
    private int|float $startOffsetY;

    /**
     * Cell coordinates of bottom-right corner of shape, e.g. 'B2'.
     */
    private string $endCoordinates;

    /**
     * Horizontal offset of bottom-right corner of shape measured in 1/1024 of column width.
     */
    private int|float $endOffsetX;

    /**
     * Vertical offset of bottom-right corner of shape measured in 1/256 of row height.
     */
    private int|float $endOffsetY;

    /**
     * Set parent Shape Group Container.
     */
    public function setParent(SpgrContainer $parent): void
=======
     * @var array
     */
    private $OPT;

    /**
     * Cell coordinates of upper-left corner of shape, e.g. 'A1'.
     *
     * @var string
     */
    private $startCoordinates;

    /**
     * Horizontal offset of upper-left corner of shape measured in 1/1024 of column width.
     *
     * @var int
     */
    private $startOffsetX;

    /**
     * Vertical offset of upper-left corner of shape measured in 1/256 of row height.
     *
     * @var int
     */
    private $startOffsetY;

    /**
     * Cell coordinates of bottom-right corner of shape, e.g. 'B2'.
     *
     * @var string
     */
    private $endCoordinates;

    /**
     * Horizontal offset of bottom-right corner of shape measured in 1/1024 of column width.
     *
     * @var int
     */
    private $endOffsetX;

    /**
     * Vertical offset of bottom-right corner of shape measured in 1/256 of row height.
     *
     * @var int
     */
    private $endOffsetY;

    /**
     * Set parent Shape Group Container.
     *
     * @param SpgrContainer $parent
     */
    public function setParent($parent): void
>>>>>>> main
    {
        $this->parent = $parent;
    }

    /**
     * Get the parent Shape Group Container.
<<<<<<< HEAD
     */
    public function getParent(): SpgrContainer
=======
     *
     * @return SpgrContainer
     */
    public function getParent()
>>>>>>> main
    {
        return $this->parent;
    }

    /**
     * Set whether this is a group shape.
<<<<<<< HEAD
     */
    public function setSpgr(bool $value): void
=======
     *
     * @param bool $value
     */
    public function setSpgr($value): void
>>>>>>> main
    {
        $this->spgr = $value;
    }

    /**
     * Get whether this is a group shape.
<<<<<<< HEAD
     */
    public function getSpgr(): bool
=======
     *
     * @return bool
     */
    public function getSpgr()
>>>>>>> main
    {
        return $this->spgr;
    }

    /**
     * Set the shape type.
<<<<<<< HEAD
     */
    public function setSpType(int $value): void
=======
     *
     * @param int $value
     */
    public function setSpType($value): void
>>>>>>> main
    {
        $this->spType = $value;
    }

    /**
     * Get the shape type.
<<<<<<< HEAD
     */
    public function getSpType(): int
=======
     *
     * @return int
     */
    public function getSpType()
>>>>>>> main
    {
        return $this->spType;
    }

    /**
     * Set the shape flag.
<<<<<<< HEAD
     */
    public function setSpFlag(int $value): void
=======
     *
     * @param int $value
     */
    public function setSpFlag($value): void
>>>>>>> main
    {
        $this->spFlag = $value;
    }

    /**
     * Get the shape flag.
<<<<<<< HEAD
     */
    public function getSpFlag(): int
=======
     *
     * @return int
     */
    public function getSpFlag()
>>>>>>> main
    {
        return $this->spFlag;
    }

    /**
     * Set the shape index.
<<<<<<< HEAD
     */
    public function setSpId(int $value): void
=======
     *
     * @param int $value
     */
    public function setSpId($value): void
>>>>>>> main
    {
        $this->spId = $value;
    }

    /**
     * Get the shape index.
<<<<<<< HEAD
     */
    public function getSpId(): int
=======
     *
     * @return int
     */
    public function getSpId()
>>>>>>> main
    {
        return $this->spId;
    }

    /**
     * Set an option for the Shape Group Container.
     *
     * @param int $property The number specifies the option
<<<<<<< HEAD
     */
    public function setOPT(int $property, mixed $value): void
=======
     * @param mixed $value
     */
    public function setOPT($property, $value): void
>>>>>>> main
    {
        $this->OPT[$property] = $value;
    }

    /**
     * Get an option for the Shape Group Container.
     *
     * @param int $property The number specifies the option
<<<<<<< HEAD
     */
    public function getOPT(int $property): mixed
    {
        return $this->OPT[$property] ?? null;
=======
     *
     * @return mixed
     */
    public function getOPT($property)
    {
        if (isset($this->OPT[$property])) {
            return $this->OPT[$property];
        }

        return null;
>>>>>>> main
    }

    /**
     * Get the collection of options.
     *
<<<<<<< HEAD
     * @return mixed[]
     */
    public function getOPTCollection(): array
=======
     * @return array
     */
    public function getOPTCollection()
>>>>>>> main
    {
        return $this->OPT;
    }

    /**
     * Set cell coordinates of upper-left corner of shape.
     *
     * @param string $value eg: 'A1'
     */
<<<<<<< HEAD
    public function setStartCoordinates(string $value): void
=======
    public function setStartCoordinates($value): void
>>>>>>> main
    {
        $this->startCoordinates = $value;
    }

    /**
     * Get cell coordinates of upper-left corner of shape.
<<<<<<< HEAD
     */
    public function getStartCoordinates(): string
=======
     *
     * @return string
     */
    public function getStartCoordinates()
>>>>>>> main
    {
        return $this->startCoordinates;
    }

    /**
     * Set offset in x-direction of upper-left corner of shape measured in 1/1024 of column width.
<<<<<<< HEAD
     */
    public function setStartOffsetX(int|float $startOffsetX): void
=======
     *
     * @param int $startOffsetX
     */
    public function setStartOffsetX($startOffsetX): void
>>>>>>> main
    {
        $this->startOffsetX = $startOffsetX;
    }

    /**
     * Get offset in x-direction of upper-left corner of shape measured in 1/1024 of column width.
<<<<<<< HEAD
     */
    public function getStartOffsetX(): int|float
=======
     *
     * @return int
     */
    public function getStartOffsetX()
>>>>>>> main
    {
        return $this->startOffsetX;
    }

    /**
     * Set offset in y-direction of upper-left corner of shape measured in 1/256 of row height.
<<<<<<< HEAD
     */
    public function setStartOffsetY(int|float $startOffsetY): void
=======
     *
     * @param int $startOffsetY
     */
    public function setStartOffsetY($startOffsetY): void
>>>>>>> main
    {
        $this->startOffsetY = $startOffsetY;
    }

    /**
     * Get offset in y-direction of upper-left corner of shape measured in 1/256 of row height.
<<<<<<< HEAD
     */
    public function getStartOffsetY(): int|float
=======
     *
     * @return int
     */
    public function getStartOffsetY()
>>>>>>> main
    {
        return $this->startOffsetY;
    }

    /**
     * Set cell coordinates of bottom-right corner of shape.
     *
     * @param string $value eg: 'A1'
     */
<<<<<<< HEAD
    public function setEndCoordinates(string $value): void
=======
    public function setEndCoordinates($value): void
>>>>>>> main
    {
        $this->endCoordinates = $value;
    }

    /**
     * Get cell coordinates of bottom-right corner of shape.
<<<<<<< HEAD
     */
    public function getEndCoordinates(): string
=======
     *
     * @return string
     */
    public function getEndCoordinates()
>>>>>>> main
    {
        return $this->endCoordinates;
    }

    /**
     * Set offset in x-direction of bottom-right corner of shape measured in 1/1024 of column width.
<<<<<<< HEAD
     */
    public function setEndOffsetX(int|float $endOffsetX): void
=======
     *
     * @param int $endOffsetX
     */
    public function setEndOffsetX($endOffsetX): void
>>>>>>> main
    {
        $this->endOffsetX = $endOffsetX;
    }

    /**
     * Get offset in x-direction of bottom-right corner of shape measured in 1/1024 of column width.
<<<<<<< HEAD
     */
    public function getEndOffsetX(): int|float
=======
     *
     * @return int
     */
    public function getEndOffsetX()
>>>>>>> main
    {
        return $this->endOffsetX;
    }

    /**
     * Set offset in y-direction of bottom-right corner of shape measured in 1/256 of row height.
<<<<<<< HEAD
     */
    public function setEndOffsetY(int|float $endOffsetY): void
=======
     *
     * @param int $endOffsetY
     */
    public function setEndOffsetY($endOffsetY): void
>>>>>>> main
    {
        $this->endOffsetY = $endOffsetY;
    }

    /**
     * Get offset in y-direction of bottom-right corner of shape measured in 1/256 of row height.
<<<<<<< HEAD
     */
    public function getEndOffsetY(): int|float
=======
     *
     * @return int
     */
    public function getEndOffsetY()
>>>>>>> main
    {
        return $this->endOffsetY;
    }

    /**
     * Get the nesting level of this spContainer. This is the number of spgrContainers between this spContainer and
     * the dgContainer. A value of 1 = immediately within first spgrContainer
     * Higher nesting level occurs if and only if spContainer is part of a shape group.
     *
     * @return int Nesting level
     */
<<<<<<< HEAD
    public function getNestingLevel(): int
=======
    public function getNestingLevel()
>>>>>>> main
    {
        $nestingLevel = 0;

        $parent = $this->getParent();
        while ($parent instanceof SpgrContainer) {
            ++$nestingLevel;
            $parent = $parent->getParent();
        }

        return $nestingLevel;
    }
}
