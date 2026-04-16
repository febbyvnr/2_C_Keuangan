<?php

namespace PhpOffice\PhpSpreadsheet\Shared\Escher;

<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;

=======
>>>>>>> main
class DggContainer
{
    /**
     * Maximum shape index of all shapes in all drawings increased by one.
<<<<<<< HEAD
     */
    private int $spIdMax;

    /**
     * Total number of drawings saved.
     */
    private int $cDgSaved;

    /**
     * Total number of shapes saved (including group shapes).
     */
    private int $cSpSaved;

    /**
     * BLIP Store Container.
     */
    private ?DggContainer\BstoreContainer $bstoreContainer = null;
=======
     *
     * @var int
     */
    private $spIdMax;

    /**
     * Total number of drawings saved.
     *
     * @var int
     */
    private $cDgSaved;

    /**
     * Total number of shapes saved (including group shapes).
     *
     * @var int
     */
    private $cSpSaved;

    /**
     * BLIP Store Container.
     *
     * @var ?DggContainer\BstoreContainer
     */
    private $bstoreContainer;
>>>>>>> main

    /**
     * Array of options for the drawing group.
     *
<<<<<<< HEAD
     * @var mixed[]
     */
    private array $OPT = [];

    /**
     * Array of identifier clusters containing information about the maximum shape identifiers.
     *
     * @var mixed[]
     */
    private array $IDCLs = [];

    /**
     * Get maximum shape index of all shapes in all drawings (plus one).
     */
    public function getSpIdMax(): int
=======
     * @var array
     */
    private $OPT = [];

    /**
     * Array of identifier clusters containg information about the maximum shape identifiers.
     *
     * @var array
     */
    private $IDCLs = [];

    /**
     * Get maximum shape index of all shapes in all drawings (plus one).
     *
     * @return int
     */
    public function getSpIdMax()
>>>>>>> main
    {
        return $this->spIdMax;
    }

    /**
     * Set maximum shape index of all shapes in all drawings (plus one).
<<<<<<< HEAD
     */
    public function setSpIdMax(int $value): void
=======
     *
     * @param int $value
     */
    public function setSpIdMax($value): void
>>>>>>> main
    {
        $this->spIdMax = $value;
    }

    /**
     * Get total number of drawings saved.
<<<<<<< HEAD
     */
    public function getCDgSaved(): int
=======
     *
     * @return int
     */
    public function getCDgSaved()
>>>>>>> main
    {
        return $this->cDgSaved;
    }

    /**
     * Set total number of drawings saved.
<<<<<<< HEAD
     */
    public function setCDgSaved(int $value): void
=======
     *
     * @param int $value
     */
    public function setCDgSaved($value): void
>>>>>>> main
    {
        $this->cDgSaved = $value;
    }

    /**
     * Get total number of shapes saved (including group shapes).
<<<<<<< HEAD
     */
    public function getCSpSaved(): int
=======
     *
     * @return int
     */
    public function getCSpSaved()
>>>>>>> main
    {
        return $this->cSpSaved;
    }

    /**
     * Set total number of shapes saved (including group shapes).
<<<<<<< HEAD
     */
    public function setCSpSaved(int $value): void
=======
     *
     * @param int $value
     */
    public function setCSpSaved($value): void
>>>>>>> main
    {
        $this->cSpSaved = $value;
    }

    /**
     * Get BLIP Store Container.
<<<<<<< HEAD
     */
    public function getBstoreContainer(): ?DggContainer\BstoreContainer
=======
     *
     * @return ?DggContainer\BstoreContainer
     */
    public function getBstoreContainer()
>>>>>>> main
    {
        return $this->bstoreContainer;
    }

    /**
<<<<<<< HEAD
     * Get BLIP Store Container.
     */
    public function getBstoreContainerOrThrow(): DggContainer\BstoreContainer
    {
        return $this->bstoreContainer ?? throw new SpreadsheetException('bstoreContainer is unexpectedly null');
    }

    /**
     * Set BLIP Store Container.
     */
    public function setBstoreContainer(DggContainer\BstoreContainer $bstoreContainer): void
=======
     * Set BLIP Store Container.
     *
     * @param DggContainer\BstoreContainer $bstoreContainer
     */
    public function setBstoreContainer($bstoreContainer): void
>>>>>>> main
    {
        $this->bstoreContainer = $bstoreContainer;
    }

    /**
     * Set an option for the drawing group.
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
     * Get an option for the drawing group.
     *
     * @param int $property The number specifies the option
<<<<<<< HEAD
     */
    public function getOPT(int $property): mixed
=======
     *
     * @return mixed
     */
    public function getOPT($property)
>>>>>>> main
    {
        if (isset($this->OPT[$property])) {
            return $this->OPT[$property];
        }

        return null;
    }

    /**
     * Get identifier clusters.
     *
<<<<<<< HEAD
     * @return mixed[]
     */
    public function getIDCLs(): array
=======
     * @return array
     */
    public function getIDCLs()
>>>>>>> main
    {
        return $this->IDCLs;
    }

    /**
     * Set identifier clusters. [<drawingId> => <max shape id>, ...].
     *
<<<<<<< HEAD
     * @param mixed[] $IDCLs
     */
    public function setIDCLs(array $IDCLs): void
=======
     * @param array $IDCLs
     */
    public function setIDCLs($IDCLs): void
>>>>>>> main
    {
        $this->IDCLs = $IDCLs;
    }
}
