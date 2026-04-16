<?php

namespace PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer\BstoreContainer\BSE;

use PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer\BstoreContainer\BSE;

class Blip
{
    /**
     * The parent BSE.
<<<<<<< HEAD
     */
    private BSE $parent;

    /**
     * Raw image data.
     */
    private string $data;

    /**
     * Get the raw image data.
     */
    public function getData(): string
=======
     *
     * @var BSE
     */
    private $parent;

    /**
     * Raw image data.
     *
     * @var string
     */
    private $data;

    /**
     * Get the raw image data.
     *
     * @return string
     */
    public function getData()
>>>>>>> main
    {
        return $this->data;
    }

    /**
     * Set the raw image data.
<<<<<<< HEAD
     */
    public function setData(string $data): void
=======
     *
     * @param string $data
     */
    public function setData($data): void
>>>>>>> main
    {
        $this->data = $data;
    }

    /**
     * Set parent BSE.
     */
    public function setParent(BSE $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Get parent BSE.
     */
    public function getParent(): BSE
    {
        return $this->parent;
    }
}
