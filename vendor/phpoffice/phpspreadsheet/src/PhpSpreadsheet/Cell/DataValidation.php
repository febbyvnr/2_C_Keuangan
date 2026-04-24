<?php

namespace PhpOffice\PhpSpreadsheet\Cell;

class DataValidation
{
    // Data validation types
    const TYPE_NONE = 'none';
    const TYPE_CUSTOM = 'custom';
    const TYPE_DATE = 'date';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_LIST = 'list';
    const TYPE_TEXTLENGTH = 'textLength';
    const TYPE_TIME = 'time';
    const TYPE_WHOLE = 'whole';

    // Data validation error styles
    const STYLE_STOP = 'stop';
    const STYLE_WARNING = 'warning';
    const STYLE_INFORMATION = 'information';

    // Data validation operators
    const OPERATOR_BETWEEN = 'between';
    const OPERATOR_EQUAL = 'equal';
    const OPERATOR_GREATERTHAN = 'greaterThan';
    const OPERATOR_GREATERTHANOREQUAL = 'greaterThanOrEqual';
    const OPERATOR_LESSTHAN = 'lessThan';
    const OPERATOR_LESSTHANOREQUAL = 'lessThanOrEqual';
    const OPERATOR_NOTBETWEEN = 'notBetween';
    const OPERATOR_NOTEQUAL = 'notEqual';
<<<<<<< HEAD
    private const DEFAULT_OPERATOR = self::OPERATOR_BETWEEN;

    /**
     * Formula 1.
     */
    private string $formula1 = '';

    /**
     * Formula 2.
     */
    private string $formula2 = '';

    /**
     * Type.
     */
    private string $type = self::TYPE_NONE;

    /**
     * Error style.
     */
    private string $errorStyle = self::STYLE_STOP;

    /**
     * Operator.
     */
    private string $operator = self::DEFAULT_OPERATOR;

    /**
     * Allow Blank.
     */
    private bool $allowBlank = false;

    /**
     * Show DropDown.
     */
    private bool $showDropDown = false;

    /**
     * Show InputMessage.
     */
    private bool $showInputMessage = false;

    /**
     * Show ErrorMessage.
     */
    private bool $showErrorMessage = false;

    /**
     * Error title.
     */
    private string $errorTitle = '';

    /**
     * Error.
     */
    private string $error = '';

    /**
     * Prompt title.
     */
    private string $promptTitle = '';

    /**
     * Prompt.
     */
    private string $prompt = '';

    /**
     * Get Formula 1.
     */
    public function getFormula1(): string
=======

    /**
     * Formula 1.
     *
     * @var string
     */
    private $formula1 = '';

    /**
     * Formula 2.
     *
     * @var string
     */
    private $formula2 = '';

    /**
     * Type.
     *
     * @var string
     */
    private $type = self::TYPE_NONE;

    /**
     * Error style.
     *
     * @var string
     */
    private $errorStyle = self::STYLE_STOP;

    /**
     * Operator.
     *
     * @var string
     */
    private $operator = self::OPERATOR_BETWEEN;

    /**
     * Allow Blank.
     *
     * @var bool
     */
    private $allowBlank = false;

    /**
     * Show DropDown.
     *
     * @var bool
     */
    private $showDropDown = false;

    /**
     * Show InputMessage.
     *
     * @var bool
     */
    private $showInputMessage = false;

    /**
     * Show ErrorMessage.
     *
     * @var bool
     */
    private $showErrorMessage = false;

    /**
     * Error title.
     *
     * @var string
     */
    private $errorTitle = '';

    /**
     * Error.
     *
     * @var string
     */
    private $error = '';

    /**
     * Prompt title.
     *
     * @var string
     */
    private $promptTitle = '';

    /**
     * Prompt.
     *
     * @var string
     */
    private $prompt = '';

    /**
     * Create a new DataValidation.
     */
    public function __construct()
    {
    }

    /**
     * Get Formula 1.
     *
     * @return string
     */
    public function getFormula1()
>>>>>>> main
    {
        return $this->formula1;
    }

    /**
     * Set Formula 1.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setFormula1(string $formula): static
=======
     * @param string $formula
     *
     * @return $this
     */
    public function setFormula1($formula)
>>>>>>> main
    {
        $this->formula1 = $formula;

        return $this;
    }

    /**
     * Get Formula 2.
<<<<<<< HEAD
     */
    public function getFormula2(): string
=======
     *
     * @return string
     */
    public function getFormula2()
>>>>>>> main
    {
        return $this->formula2;
    }

    /**
     * Set Formula 2.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setFormula2(string $formula): static
=======
     * @param string $formula
     *
     * @return $this
     */
    public function setFormula2($formula)
>>>>>>> main
    {
        $this->formula2 = $formula;

        return $this;
    }

    /**
     * Get Type.
<<<<<<< HEAD
     */
    public function getType(): string
=======
     *
     * @return string
     */
    public function getType()
>>>>>>> main
    {
        return $this->type;
    }

    /**
     * Set Type.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setType(string $type): static
=======
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
>>>>>>> main
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get Error style.
<<<<<<< HEAD
     */
    public function getErrorStyle(): string
=======
     *
     * @return string
     */
    public function getErrorStyle()
>>>>>>> main
    {
        return $this->errorStyle;
    }

    /**
     * Set Error style.
     *
     * @param string $errorStyle see self::STYLE_*
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setErrorStyle(string $errorStyle): static
=======
    public function setErrorStyle($errorStyle)
>>>>>>> main
    {
        $this->errorStyle = $errorStyle;

        return $this;
    }

    /**
     * Get Operator.
<<<<<<< HEAD
     */
    public function getOperator(): string
=======
     *
     * @return string
     */
    public function getOperator()
>>>>>>> main
    {
        return $this->operator;
    }

    /**
     * Set Operator.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setOperator(string $operator): static
    {
        $this->operator = ($operator === '') ? self::DEFAULT_OPERATOR : $operator;
=======
     * @param string $operator
     *
     * @return $this
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
>>>>>>> main

        return $this;
    }

    /**
     * Get Allow Blank.
<<<<<<< HEAD
     */
    public function getAllowBlank(): bool
=======
     *
     * @return bool
     */
    public function getAllowBlank()
>>>>>>> main
    {
        return $this->allowBlank;
    }

    /**
     * Set Allow Blank.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setAllowBlank(bool $allowBlank): static
=======
     * @param bool $allowBlank
     *
     * @return $this
     */
    public function setAllowBlank($allowBlank)
>>>>>>> main
    {
        $this->allowBlank = $allowBlank;

        return $this;
    }

    /**
     * Get Show DropDown.
<<<<<<< HEAD
     */
    public function getShowDropDown(): bool
=======
     *
     * @return bool
     */
    public function getShowDropDown()
>>>>>>> main
    {
        return $this->showDropDown;
    }

    /**
     * Set Show DropDown.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setShowDropDown(bool $showDropDown): static
=======
     * @param bool $showDropDown
     *
     * @return $this
     */
    public function setShowDropDown($showDropDown)
>>>>>>> main
    {
        $this->showDropDown = $showDropDown;

        return $this;
    }

    /**
     * Get Show InputMessage.
<<<<<<< HEAD
     */
    public function getShowInputMessage(): bool
=======
     *
     * @return bool
     */
    public function getShowInputMessage()
>>>>>>> main
    {
        return $this->showInputMessage;
    }

    /**
     * Set Show InputMessage.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setShowInputMessage(bool $showInputMessage): static
=======
     * @param bool $showInputMessage
     *
     * @return $this
     */
    public function setShowInputMessage($showInputMessage)
>>>>>>> main
    {
        $this->showInputMessage = $showInputMessage;

        return $this;
    }

    /**
     * Get Show ErrorMessage.
<<<<<<< HEAD
     */
    public function getShowErrorMessage(): bool
=======
     *
     * @return bool
     */
    public function getShowErrorMessage()
>>>>>>> main
    {
        return $this->showErrorMessage;
    }

    /**
     * Set Show ErrorMessage.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setShowErrorMessage(bool $showErrorMessage): static
=======
     * @param bool $showErrorMessage
     *
     * @return $this
     */
    public function setShowErrorMessage($showErrorMessage)
>>>>>>> main
    {
        $this->showErrorMessage = $showErrorMessage;

        return $this;
    }

    /**
     * Get Error title.
<<<<<<< HEAD
     */
    public function getErrorTitle(): string
=======
     *
     * @return string
     */
    public function getErrorTitle()
>>>>>>> main
    {
        return $this->errorTitle;
    }

    /**
     * Set Error title.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setErrorTitle(string $errorTitle): static
=======
     * @param string $errorTitle
     *
     * @return $this
     */
    public function setErrorTitle($errorTitle)
>>>>>>> main
    {
        $this->errorTitle = $errorTitle;

        return $this;
    }

    /**
     * Get Error.
<<<<<<< HEAD
     */
    public function getError(): string
=======
     *
     * @return string
     */
    public function getError()
>>>>>>> main
    {
        return $this->error;
    }

    /**
     * Set Error.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setError(string $error): static
=======
     * @param string $error
     *
     * @return $this
     */
    public function setError($error)
>>>>>>> main
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get Prompt title.
<<<<<<< HEAD
     */
    public function getPromptTitle(): string
=======
     *
     * @return string
     */
    public function getPromptTitle()
>>>>>>> main
    {
        return $this->promptTitle;
    }

    /**
     * Set Prompt title.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setPromptTitle(string $promptTitle): static
=======
     * @param string $promptTitle
     *
     * @return $this
     */
    public function setPromptTitle($promptTitle)
>>>>>>> main
    {
        $this->promptTitle = $promptTitle;

        return $this;
    }

    /**
     * Get Prompt.
<<<<<<< HEAD
     */
    public function getPrompt(): string
=======
     *
     * @return string
     */
    public function getPrompt()
>>>>>>> main
    {
        return $this->prompt;
    }

    /**
     * Set Prompt.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setPrompt(string $prompt): static
=======
     * @param string $prompt
     *
     * @return $this
     */
    public function setPrompt($prompt)
>>>>>>> main
    {
        $this->prompt = $prompt;

        return $this;
    }

    /**
     * Get hash code.
     *
     * @return string Hash code
     */
<<<<<<< HEAD
    public function getHashCode(): string
    {
        return md5(
            $this->formula1
            . $this->formula2
            . $this->type
            . $this->errorStyle
            . $this->operator
            . ($this->allowBlank ? 't' : 'f')
            . ($this->showDropDown ? 't' : 'f')
            . ($this->showInputMessage ? 't' : 'f')
            . ($this->showErrorMessage ? 't' : 'f')
            . $this->errorTitle
            . $this->error
            . $this->promptTitle
            . $this->prompt
            . $this->sqref
            . __CLASS__
        );
    }

    private ?string $sqref = null;
=======
    public function getHashCode()
    {
        return md5(
            $this->formula1 .
            $this->formula2 .
            $this->type .
            $this->errorStyle .
            $this->operator .
            ($this->allowBlank ? 't' : 'f') .
            ($this->showDropDown ? 't' : 'f') .
            ($this->showInputMessage ? 't' : 'f') .
            ($this->showErrorMessage ? 't' : 'f') .
            $this->errorTitle .
            $this->error .
            $this->promptTitle .
            $this->prompt .
            $this->sqref .
            __CLASS__
        );
    }

    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     */
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (is_object($value)) {
                $this->$key = clone $value;
            } else {
                $this->$key = $value;
            }
        }
    }

    /** @var ?string */
    private $sqref;
>>>>>>> main

    public function getSqref(): ?string
    {
        return $this->sqref;
    }

    public function setSqref(?string $str): self
    {
        $this->sqref = $str;

        return $this;
    }
}
