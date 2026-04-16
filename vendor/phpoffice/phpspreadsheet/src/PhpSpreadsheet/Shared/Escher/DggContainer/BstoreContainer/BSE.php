<?php

namespace PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer\BstoreContainer;

use PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer\BstoreContainer;

class BSE
{
    const BLIPTYPE_ERROR = 0x00;
    const BLIPTYPE_UNKNOWN = 0x01;
    const BLIPTYPE_EMF = 0x02;
    const BLIPTYPE_WMF = 0x03;
    const BLIPTYPE_PICT = 0x04;
    const BLIPTYPE_JPEG = 0x05;
    const BLIPTYPE_PNG = 0x06;
    const BLIPTYPE_DIB = 0x07;
    const BLIPTYPE_TIFF = 0x11;
    const BLIPTYPE_CMYKJPEG = 0x12;

    /**
     * The parent BLIP Store Entry Container.
<<<<<<< HEAD
     * Property is currently unused.
     */
    private BstoreContainer $parent;

    /**
     * The BLIP (Big Large Image or Picture).
     */
    private ?BSE\Blip $blip = null;

    /**
     * The BLIP type.
     */
    private int $blipType;
=======
     * Property is never currently read.
     *
     * @var BstoreContainer
     */
    private $parent; // @phpstan-ignore-line

    /**
     * The BLIP (Big Large Image or Picture).
     *
     * @var ?BSE\Blip
     */
    private $blip;

    /**
     * The BLIP type.
     *
     * @var int
     */
    private $blipType;
>>>>>>> main

    /**
     * Set parent BLIP Store Entry Container.
     */
    public function setParent(BstoreContainer $parent): void
    {
        $this->parent = $parent;
    }

<<<<<<< HEAD
    public function getParent(): BstoreContainer
    {
        return $this->parent;
    }

    /**
     * Get the BLIP.
     */
    public function getBlip(): ?BSE\Blip
=======
    /**
     * Get the BLIP.
     *
     * @return ?BSE\Blip
     */
    public function getBlip()
>>>>>>> main
    {
        return $this->blip;
    }

    /**
     * Set the BLIP.
     */
    public function setBlip(BSE\Blip $blip): void
    {
        $this->blip = $blip;
        $blip->setParent($this);
    }

    /**
     * Get the BLIP type.
<<<<<<< HEAD
     */
    public function getBlipType(): int
=======
     *
     * @return int
     */
    public function getBlipType()
>>>>>>> main
    {
        return $this->blipType;
    }

    /**
     * Set the BLIP type.
<<<<<<< HEAD
     */
    public function setBlipType(int $blipType): void
=======
     *
     * @param int $blipType
     */
    public function setBlipType($blipType): void
>>>>>>> main
    {
        $this->blipType = $blipType;
    }
}
