<?php

namespace PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting;

class ConditionalFormatValueObject
{
<<<<<<< HEAD
    private string $type;

    private null|float|int|string $value;

    private ?string $cellFormula;

    /**
     * For icon sets, determines whether this threshold value uses the greater
     * than or equal to operator. False indicates 'greater than' is used instead
     * of 'greater than or equal to'.
     */
    private ?bool $greaterThanOrEqual = null;

    public function __construct(string $type, null|float|int|string $value = null, ?string $cellFormula = null)
=======
    /** @var mixed */
    private $type;

    /** @var mixed */
    private $value;

    /** @var mixed */
    private $cellFormula;

    /**
     * ConditionalFormatValueObject constructor.
     *
     * @param mixed $type
     * @param mixed $value
     * @param null|mixed $cellFormula
     */
    public function __construct($type, $value = null, $cellFormula = null)
>>>>>>> main
    {
        $this->type = $type;
        $this->value = $value;
        $this->cellFormula = $cellFormula;
    }

<<<<<<< HEAD
    public function getType(): string
=======
    /**
     * @return mixed
     */
    public function getType()
>>>>>>> main
    {
        return $this->type;
    }

<<<<<<< HEAD
    public function setType(string $type): self
=======
    /**
     * @param mixed $type
     */
    public function setType($type): self
>>>>>>> main
    {
        $this->type = $type;

        return $this;
    }

<<<<<<< HEAD
    public function getValue(): null|float|int|string
=======
    /**
     * @return mixed
     */
    public function getValue()
>>>>>>> main
    {
        return $this->value;
    }

<<<<<<< HEAD
    public function setValue(null|float|int|string $value): self
=======
    /**
     * @param mixed $value
     */
    public function setValue($value): self
>>>>>>> main
    {
        $this->value = $value;

        return $this;
    }

<<<<<<< HEAD
    public function getCellFormula(): ?string
=======
    /**
     * @return mixed
     */
    public function getCellFormula()
>>>>>>> main
    {
        return $this->cellFormula;
    }

<<<<<<< HEAD
    public function setCellFormula(?string $cellFormula): self
=======
    /**
     * @param mixed $cellFormula
     */
    public function setCellFormula($cellFormula): self
>>>>>>> main
    {
        $this->cellFormula = $cellFormula;

        return $this;
    }
<<<<<<< HEAD

    public function getGreaterThanOrEqual(): ?bool
    {
        return $this->greaterThanOrEqual;
    }

    public function setGreaterThanOrEqual(?bool $greaterThanOrEqual): self
    {
        $this->greaterThanOrEqual = $greaterThanOrEqual;

        return $this;
    }
=======
>>>>>>> main
}
