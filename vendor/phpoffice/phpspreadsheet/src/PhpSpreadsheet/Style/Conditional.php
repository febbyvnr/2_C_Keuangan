<?php

namespace PhpOffice\PhpSpreadsheet\Style;

use PhpOffice\PhpSpreadsheet\IComparable;
<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalColorScale;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalDataBar;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalIconSet;
=======
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\ConditionalDataBar;
>>>>>>> main

class Conditional implements IComparable
{
    // Condition types
    const CONDITION_NONE = 'none';
    const CONDITION_BEGINSWITH = 'beginsWith';
    const CONDITION_CELLIS = 'cellIs';
<<<<<<< HEAD
    const CONDITION_COLORSCALE = 'colorScale';
=======
>>>>>>> main
    const CONDITION_CONTAINSBLANKS = 'containsBlanks';
    const CONDITION_CONTAINSERRORS = 'containsErrors';
    const CONDITION_CONTAINSTEXT = 'containsText';
    const CONDITION_DATABAR = 'dataBar';
    const CONDITION_ENDSWITH = 'endsWith';
    const CONDITION_EXPRESSION = 'expression';
    const CONDITION_NOTCONTAINSBLANKS = 'notContainsBlanks';
    const CONDITION_NOTCONTAINSERRORS = 'notContainsErrors';
    const CONDITION_NOTCONTAINSTEXT = 'notContainsText';
    const CONDITION_TIMEPERIOD = 'timePeriod';
    const CONDITION_DUPLICATES = 'duplicateValues';
    const CONDITION_UNIQUE = 'uniqueValues';
<<<<<<< HEAD
    const CONDITION_ICONSET = 'iconSet';
=======
>>>>>>> main

    private const CONDITION_TYPES = [
        self::CONDITION_BEGINSWITH,
        self::CONDITION_CELLIS,
<<<<<<< HEAD
        self::CONDITION_COLORSCALE,
=======
>>>>>>> main
        self::CONDITION_CONTAINSBLANKS,
        self::CONDITION_CONTAINSERRORS,
        self::CONDITION_CONTAINSTEXT,
        self::CONDITION_DATABAR,
        self::CONDITION_DUPLICATES,
        self::CONDITION_ENDSWITH,
        self::CONDITION_EXPRESSION,
        self::CONDITION_NONE,
        self::CONDITION_NOTCONTAINSBLANKS,
        self::CONDITION_NOTCONTAINSERRORS,
        self::CONDITION_NOTCONTAINSTEXT,
        self::CONDITION_TIMEPERIOD,
        self::CONDITION_UNIQUE,
<<<<<<< HEAD
        self::CONDITION_ICONSET,
=======
>>>>>>> main
    ];

    // Operator types
    const OPERATOR_NONE = '';
    const OPERATOR_BEGINSWITH = 'beginsWith';
    const OPERATOR_ENDSWITH = 'endsWith';
    const OPERATOR_EQUAL = 'equal';
    const OPERATOR_GREATERTHAN = 'greaterThan';
    const OPERATOR_GREATERTHANOREQUAL = 'greaterThanOrEqual';
    const OPERATOR_LESSTHAN = 'lessThan';
    const OPERATOR_LESSTHANOREQUAL = 'lessThanOrEqual';
    const OPERATOR_NOTEQUAL = 'notEqual';
    const OPERATOR_CONTAINSTEXT = 'containsText';
    const OPERATOR_NOTCONTAINS = 'notContains';
    const OPERATOR_BETWEEN = 'between';
    const OPERATOR_NOTBETWEEN = 'notBetween';

    const TIMEPERIOD_TODAY = 'today';
    const TIMEPERIOD_YESTERDAY = 'yesterday';
    const TIMEPERIOD_TOMORROW = 'tomorrow';
    const TIMEPERIOD_LAST_7_DAYS = 'last7Days';
    const TIMEPERIOD_LAST_WEEK = 'lastWeek';
    const TIMEPERIOD_THIS_WEEK = 'thisWeek';
    const TIMEPERIOD_NEXT_WEEK = 'nextWeek';
    const TIMEPERIOD_LAST_MONTH = 'lastMonth';
    const TIMEPERIOD_THIS_MONTH = 'thisMonth';
    const TIMEPERIOD_NEXT_MONTH = 'nextMonth';

    /**
     * Condition type.
<<<<<<< HEAD
     */
    private string $conditionType = self::CONDITION_NONE;

    /**
     * Operator type.
     */
    private string $operatorType = self::OPERATOR_NONE;

    /**
     * Text.
     */
    private string $text = '';

    /**
     * Stop on this condition, if it matches.
     */
    private bool $stopIfTrue = false;
=======
     *
     * @var string
     */
    private $conditionType = self::CONDITION_NONE;

    /**
     * Operator type.
     *
     * @var string
     */
    private $operatorType = self::OPERATOR_NONE;

    /**
     * Text.
     *
     * @var string
     */
    private $text;

    /**
     * Stop on this condition, if it matches.
     *
     * @var bool
     */
    private $stopIfTrue = false;
>>>>>>> main

    /**
     * Condition.
     *
     * @var (bool|float|int|string)[]
     */
<<<<<<< HEAD
    private array $condition = [];

    private ?ConditionalDataBar $dataBar = null;

    private ?ConditionalColorScale $colorScale = null;

    private ?ConditionalIconSet $iconSet = null;

    private Style $style;

    private bool $noFormatSet = false;

    private int $priority = 0;
=======
    private $condition = [];

    /**
     * @var ConditionalDataBar
     */
    private $dataBar;

    /**
     * Style.
     *
     * @var Style
     */
    private $style;

    /** @var bool */
    private $noFormatSet = false;
>>>>>>> main

    /**
     * Create a new Conditional.
     */
    public function __construct()
    {
        // Initialise values
        $this->style = new Style(false, true);
    }

<<<<<<< HEAD
    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

=======
>>>>>>> main
    public function getNoFormatSet(): bool
    {
        return $this->noFormatSet;
    }

    public function setNoFormatSet(bool $noFormatSet): self
    {
        $this->noFormatSet = $noFormatSet;

        return $this;
    }

    /**
     * Get Condition type.
<<<<<<< HEAD
     */
    public function getConditionType(): string
=======
     *
     * @return string
     */
    public function getConditionType()
>>>>>>> main
    {
        return $this->conditionType;
    }

    /**
     * Set Condition type.
     *
     * @param string $type Condition type, see self::CONDITION_*
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setConditionType(string $type): static
=======
    public function setConditionType($type)
>>>>>>> main
    {
        $this->conditionType = $type;

        return $this;
    }

    /**
     * Get Operator type.
<<<<<<< HEAD
     */
    public function getOperatorType(): string
=======
     *
     * @return string
     */
    public function getOperatorType()
>>>>>>> main
    {
        return $this->operatorType;
    }

    /**
     * Set Operator type.
     *
     * @param string $type Conditional operator type, see self::OPERATOR_*
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setOperatorType(string $type): static
=======
    public function setOperatorType($type)
>>>>>>> main
    {
        $this->operatorType = $type;

        return $this;
    }

    /**
     * Get text.
<<<<<<< HEAD
     */
    public function getText(): string
=======
     *
     * @return string
     */
    public function getText()
>>>>>>> main
    {
        return $this->text;
    }

    /**
     * Set text.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setText(string $text): static
=======
     * @param string $text
     *
     * @return $this
     */
    public function setText($text)
>>>>>>> main
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get StopIfTrue.
<<<<<<< HEAD
     */
    public function getStopIfTrue(): bool
=======
     *
     * @return bool
     */
    public function getStopIfTrue()
>>>>>>> main
    {
        return $this->stopIfTrue;
    }

    /**
     * Set StopIfTrue.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setStopIfTrue(bool $stopIfTrue): static
=======
     * @param bool $stopIfTrue
     *
     * @return $this
     */
    public function setStopIfTrue($stopIfTrue)
>>>>>>> main
    {
        $this->stopIfTrue = $stopIfTrue;

        return $this;
    }

    /**
     * Get Conditions.
     *
     * @return (bool|float|int|string)[]
     */
<<<<<<< HEAD
    public function getConditions(): array
=======
    public function getConditions()
>>>>>>> main
    {
        return $this->condition;
    }

    /**
     * Set Conditions.
     *
<<<<<<< HEAD
     * @param bool|(bool|float|int|string)[]|float|int|string $conditions Condition
     *
     * @return $this
     */
    public function setConditions($conditions): static
=======
     * @param (bool|float|int|string)[]|bool|float|int|string $conditions Condition
     *
     * @return $this
     */
    public function setConditions($conditions)
>>>>>>> main
    {
        if (!is_array($conditions)) {
            $conditions = [$conditions];
        }
        $this->condition = $conditions;

        return $this;
    }

    /**
     * Add Condition.
     *
     * @param bool|float|int|string $condition Condition
     *
     * @return $this
     */
<<<<<<< HEAD
    public function addCondition($condition): static
=======
    public function addCondition($condition)
>>>>>>> main
    {
        $this->condition[] = $condition;

        return $this;
    }

    /**
     * Get Style.
<<<<<<< HEAD
     */
    public function getStyle(mixed $cellData = null): Style
    {
        if ($this->conditionType === self::CONDITION_COLORSCALE && $cellData !== null && $this->colorScale !== null && is_numeric($cellData)) {
            $style = new Style(isConditional: true);
            $style->getFill()->setFillType(Fill::FILL_SOLID);
            $style->getFill()->getStartColor()->setARGB($this->colorScale->getColorForValue((float) $cellData));

            return $style;
        }

=======
     *
     * @return Style
     */
    public function getStyle()
    {
>>>>>>> main
        return $this->style;
    }

    /**
     * Set Style.
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setStyle(Style $style): static
=======
    public function setStyle(Style $style)
>>>>>>> main
    {
        $this->style = $style;

        return $this;
    }

<<<<<<< HEAD
    public function getDataBar(): ?ConditionalDataBar
=======
    /**
     * get DataBar.
     *
     * @return null|ConditionalDataBar
     */
    public function getDataBar()
>>>>>>> main
    {
        return $this->dataBar;
    }

<<<<<<< HEAD
    public function setDataBar(ConditionalDataBar $dataBar): static
=======
    /**
     * set DataBar.
     *
     * @return $this
     */
    public function setDataBar(ConditionalDataBar $dataBar)
>>>>>>> main
    {
        $this->dataBar = $dataBar;

        return $this;
    }

<<<<<<< HEAD
    public function getColorScale(): ?ConditionalColorScale
    {
        return $this->colorScale;
    }

    public function setColorScale(ConditionalColorScale $colorScale): static
    {
        $this->colorScale = $colorScale;

        return $this;
    }

    public function getIconSet(): ?ConditionalIconSet
    {
        return $this->iconSet;
    }

    public function setIconSet(ConditionalIconSet $iconSet): static
    {
        $this->iconSet = $iconSet;

        return $this;
    }

=======
>>>>>>> main
    /**
     * Get hash code.
     *
     * @return string Hash code
     */
<<<<<<< HEAD
    public function getHashCode(): string
    {
        return md5(
            $this->conditionType
                . $this->operatorType
                . implode(';', $this->condition)
                . $this->style->getHashCode()
                . __CLASS__
=======
    public function getHashCode()
    {
        return md5(
            $this->conditionType .
            $this->operatorType .
            implode(';', $this->condition) .
            $this->style->getHashCode() .
            __CLASS__
>>>>>>> main
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

    /**
     * Verify if param is valid condition type.
     */
    public static function isValidConditionType(string $type): bool
    {
        return in_array($type, self::CONDITION_TYPES);
    }
}
