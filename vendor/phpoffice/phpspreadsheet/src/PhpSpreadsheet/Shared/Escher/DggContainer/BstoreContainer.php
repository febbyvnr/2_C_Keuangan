<?php

namespace PhpOffice\PhpSpreadsheet\Shared\Escher\DggContainer;

class BstoreContainer
{
    /**
     * BLIP Store Entries. Each of them holds one BLIP (Big Large Image or Picture).
     *
     * @var BstoreContainer\BSE[]
     */
<<<<<<< HEAD
    private array $BSECollection = [];
=======
    private $BSECollection = [];
>>>>>>> main

    /**
     * Add a BLIP Store Entry.
     */
    public function addBSE(BstoreContainer\BSE $BSE): void
    {
        $this->BSECollection[] = $BSE;
        $BSE->setParent($this);
    }

    /**
     * Get the collection of BLIP Store Entries.
     *
     * @return BstoreContainer\BSE[]
     */
<<<<<<< HEAD
    public function getBSECollection(): array
=======
    public function getBSECollection()
>>>>>>> main
    {
        return $this->BSECollection;
    }
}
