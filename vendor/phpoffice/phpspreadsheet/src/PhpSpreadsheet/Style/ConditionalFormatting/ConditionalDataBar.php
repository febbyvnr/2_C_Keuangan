<?php

namespace PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting;

class ConditionalDataBar
{
<<<<<<< HEAD
    private ?bool $showValue = null;

    private ?ConditionalFormatValueObject $minimumConditionalFormatValueObject = null;

    private ?ConditionalFormatValueObject $maximumConditionalFormatValueObject = null;

    private string $color = '';

    private ?ConditionalFormattingRuleExtension $conditionalFormattingRuleExt = null;

    public function getShowValue(): ?bool
=======
    /** <dataBar> attribute  */

    /** @var null|bool */
    private $showValue;

    /** <dataBar> children */

    /** @var ?ConditionalFormatValueObject */
    private $minimumConditionalFormatValueObject;

    /** @var ?ConditionalFormatValueObject */
    private $maximumConditionalFormatValueObject;

    /** @var string */
    private $color;

    /** <extLst> */

    /** @var ?ConditionalFormattingRuleExtension */
    private $conditionalFormattingRuleExt;

    /**
     * @return null|bool
     */
    public function getShowValue()
>>>>>>> main
    {
        return $this->showValue;
    }

<<<<<<< HEAD
    public function setShowValue(bool $showValue): self
=======
    /**
     * @param bool $showValue
     */
    public function setShowValue($showValue): self
>>>>>>> main
    {
        $this->showValue = $showValue;

        return $this;
    }

    public function getMinimumConditionalFormatValueObject(): ?ConditionalFormatValueObject
    {
        return $this->minimumConditionalFormatValueObject;
    }

    public function setMinimumConditionalFormatValueObject(ConditionalFormatValueObject $minimumConditionalFormatValueObject): self
    {
        $this->minimumConditionalFormatValueObject = $minimumConditionalFormatValueObject;

        return $this;
    }

    public function getMaximumConditionalFormatValueObject(): ?ConditionalFormatValueObject
    {
        return $this->maximumConditionalFormatValueObject;
    }

    public function setMaximumConditionalFormatValueObject(ConditionalFormatValueObject $maximumConditionalFormatValueObject): self
    {
        $this->maximumConditionalFormatValueObject = $maximumConditionalFormatValueObject;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getConditionalFormattingRuleExt(): ?ConditionalFormattingRuleExtension
    {
        return $this->conditionalFormattingRuleExt;
    }

    public function setConditionalFormattingRuleExt(ConditionalFormattingRuleExtension $conditionalFormattingRuleExt): self
    {
        $this->conditionalFormattingRuleExt = $conditionalFormattingRuleExt;

        return $this;
    }
}
