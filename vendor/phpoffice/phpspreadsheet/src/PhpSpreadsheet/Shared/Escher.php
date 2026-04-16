<?php

namespace PhpOffice\PhpSpreadsheet\Shared;

<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;

=======
>>>>>>> main
class Escher
{
    /**
     * Drawing Group Container.
<<<<<<< HEAD
     */
    private ?Escher\DggContainer $dggContainer = null;

    /**
     * Drawing Container.
     */
    private ?Escher\DgContainer $dgContainer = null;

    /**
     * Get Drawing Group Container.
     */
    public function getDggContainer(): ?Escher\DggContainer
=======
     *
     * @var ?Escher\DggContainer
     */
    private $dggContainer;

    /**
     * Drawing Container.
     *
     * @var ?Escher\DgContainer
     */
    private $dgContainer;

    /**
     * Get Drawing Group Container.
     *
     * @return ?Escher\DggContainer
     */
    public function getDggContainer()
>>>>>>> main
    {
        return $this->dggContainer;
    }

    /**
<<<<<<< HEAD
     * Get Drawing Group Container.
     */
    public function getDggContainerOrThrow(): Escher\DggContainer
    {
        return $this->dggContainer ?? throw new SpreadsheetException('dggContainer is unexpectedly null');
    }

    /**
     * Set Drawing Group Container.
     */
    public function setDggContainer(Escher\DggContainer $dggContainer): Escher\DggContainer
=======
     * Set Drawing Group Container.
     *
     * @param Escher\DggContainer $dggContainer
     *
     * @return Escher\DggContainer
     */
    public function setDggContainer($dggContainer)
>>>>>>> main
    {
        return $this->dggContainer = $dggContainer;
    }

    /**
     * Get Drawing Container.
<<<<<<< HEAD
     */
    public function getDgContainer(): ?Escher\DgContainer
=======
     *
     * @return ?Escher\DgContainer
     */
    public function getDgContainer()
>>>>>>> main
    {
        return $this->dgContainer;
    }

    /**
<<<<<<< HEAD
     * Get Drawing Container.
     */
    public function getDgContainerOrThrow(): Escher\DgContainer
    {
        return $this->dgContainer ?? throw new SpreadsheetException('dgContainer is unexpectedly null');
    }

    /**
     * Set Drawing Container.
     */
    public function setDgContainer(Escher\DgContainer $dgContainer): Escher\DgContainer
=======
     * Set Drawing Container.
     *
     * @param Escher\DgContainer $dgContainer
     *
     * @return Escher\DgContainer
     */
    public function setDgContainer($dgContainer)
>>>>>>> main
    {
        return $this->dgContainer = $dgContainer;
    }
}
