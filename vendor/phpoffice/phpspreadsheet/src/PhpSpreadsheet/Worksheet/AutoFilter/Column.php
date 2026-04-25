<?php

namespace PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter;

use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter;

class Column
{
    const AUTOFILTER_FILTERTYPE_FILTER = 'filters';
    const AUTOFILTER_FILTERTYPE_CUSTOMFILTER = 'customFilters';
    //    Supports no more than 2 rules, with an And/Or join criteria
    //        if more than 1 rule is defined
    const AUTOFILTER_FILTERTYPE_DYNAMICFILTER = 'dynamicFilter';
    //    Even though the filter rule is constant, the filtered data can vary
    //        e.g. filtered by date = TODAY
    const AUTOFILTER_FILTERTYPE_TOPTENFILTER = 'top10';

    /**
     * Types of autofilter rules.
     *
     * @var string[]
     */
<<<<<<< HEAD
    private static array $filterTypes = [
=======
    private static $filterTypes = [
>>>>>>> main
        //    Currently we're not handling
        //        colorFilter
        //        extLst
        //        iconFilter
        self::AUTOFILTER_FILTERTYPE_FILTER,
        self::AUTOFILTER_FILTERTYPE_CUSTOMFILTER,
        self::AUTOFILTER_FILTERTYPE_DYNAMICFILTER,
        self::AUTOFILTER_FILTERTYPE_TOPTENFILTER,
    ];

    // Multiple Rule Connections
    const AUTOFILTER_COLUMN_JOIN_AND = 'and';
    const AUTOFILTER_COLUMN_JOIN_OR = 'or';

    /**
     * Join options for autofilter rules.
     *
     * @var string[]
     */
<<<<<<< HEAD
    private static array $ruleJoins = [
=======
    private static $ruleJoins = [
>>>>>>> main
        self::AUTOFILTER_COLUMN_JOIN_AND,
        self::AUTOFILTER_COLUMN_JOIN_OR,
    ];

    /**
     * Autofilter.
<<<<<<< HEAD
     */
    private ?AutoFilter $parent;

    /**
     * Autofilter Column Index.
     */
    private string $columnIndex;

    /**
     * Autofilter Column Filter Type.
     */
    private string $filterType = self::AUTOFILTER_FILTERTYPE_FILTER;

    /**
     * Autofilter Multiple Rules And/Or.
     */
    private string $join = self::AUTOFILTER_COLUMN_JOIN_OR;
=======
     *
     * @var null|AutoFilter
     */
    private $parent;

    /**
     * Autofilter Column Index.
     *
     * @var string
     */
    private $columnIndex = '';

    /**
     * Autofilter Column Filter Type.
     *
     * @var string
     */
    private $filterType = self::AUTOFILTER_FILTERTYPE_FILTER;

    /**
     * Autofilter Multiple Rules And/Or.
     *
     * @var string
     */
    private $join = self::AUTOFILTER_COLUMN_JOIN_OR;
>>>>>>> main

    /**
     * Autofilter Column Rules.
     *
     * @var Column\Rule[]
     */
<<<<<<< HEAD
    private array $ruleset = [];
=======
    private $ruleset = [];
>>>>>>> main

    /**
     * Autofilter Column Dynamic Attributes.
     *
<<<<<<< HEAD
     * @var (float|int|string)[]
     */
    private array $attributes = [];
=======
     * @var mixed[]
     */
    private $attributes = [];
>>>>>>> main

    /**
     * Create a new Column.
     *
     * @param string $column Column (e.g. A)
<<<<<<< HEAD
     * @param ?AutoFilter $parent Autofilter for this column
     */
    public function __construct(string $column, ?AutoFilter $parent = null)
=======
     * @param AutoFilter $parent Autofilter for this column
     */
    public function __construct($column, ?AutoFilter $parent = null)
>>>>>>> main
    {
        $this->columnIndex = $column;
        $this->parent = $parent;
    }

    public function setEvaluatedFalse(): void
    {
        if ($this->parent !== null) {
            $this->parent->setEvaluated(false);
        }
    }

    /**
     * Get AutoFilter column index as string eg: 'A'.
<<<<<<< HEAD
     */
    public function getColumnIndex(): string
=======
     *
     * @return string
     */
    public function getColumnIndex()
>>>>>>> main
    {
        return $this->columnIndex;
    }

    /**
     * Set AutoFilter column index as string eg: 'A'.
     *
     * @param string $column Column (e.g. A)
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setColumnIndex(string $column): static
=======
    public function setColumnIndex($column)
>>>>>>> main
    {
        $this->setEvaluatedFalse();
        // Uppercase coordinate
        $column = strtoupper($column);
        if ($this->parent !== null) {
            $this->parent->testColumnInRange($column);
        }

        $this->columnIndex = $column;

        return $this;
    }

    /**
     * Get this Column's AutoFilter Parent.
<<<<<<< HEAD
     */
    public function getParent(): ?AutoFilter
=======
     *
     * @return null|AutoFilter
     */
    public function getParent()
>>>>>>> main
    {
        return $this->parent;
    }

    /**
     * Set this Column's AutoFilter Parent.
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setParent(?AutoFilter $parent = null): static
=======
    public function setParent(?AutoFilter $parent = null)
>>>>>>> main
    {
        $this->setEvaluatedFalse();
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get AutoFilter Type.
<<<<<<< HEAD
     */
    public function getFilterType(): string
=======
     *
     * @return string
     */
    public function getFilterType()
>>>>>>> main
    {
        return $this->filterType;
    }

    /**
     * Set AutoFilter Type.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setFilterType(string $filterType): static
=======
     * @param string $filterType
     *
     * @return $this
     */
    public function setFilterType($filterType)
>>>>>>> main
    {
        $this->setEvaluatedFalse();
        if (!in_array($filterType, self::$filterTypes)) {
            throw new PhpSpreadsheetException('Invalid filter type for column AutoFilter.');
        }
        if ($filterType === self::AUTOFILTER_FILTERTYPE_CUSTOMFILTER && count($this->ruleset) > 2) {
            throw new PhpSpreadsheetException('No more than 2 rules are allowed in a Custom Filter');
        }

        $this->filterType = $filterType;

        return $this;
    }

    /**
     * Get AutoFilter Multiple Rules And/Or Join.
<<<<<<< HEAD
     */
    public function getJoin(): string
=======
     *
     * @return string
     */
    public function getJoin()
>>>>>>> main
    {
        return $this->join;
    }

    /**
     * Set AutoFilter Multiple Rules And/Or.
     *
     * @param string $join And/Or
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setJoin(string $join): static
=======
    public function setJoin($join)
>>>>>>> main
    {
        $this->setEvaluatedFalse();
        // Lowercase And/Or
        $join = strtolower($join);
        if (!in_array($join, self::$ruleJoins)) {
            throw new PhpSpreadsheetException('Invalid rule connection for column AutoFilter.');
        }

        $this->join = $join;

        return $this;
    }

    /**
     * Set AutoFilter Attributes.
     *
<<<<<<< HEAD
     * @param (float|int|string)[] $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes): static
=======
     * @param mixed[] $attributes
     *
     * @return $this
     */
    public function setAttributes($attributes)
>>>>>>> main
    {
        $this->setEvaluatedFalse();
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Set An AutoFilter Attribute.
     *
     * @param string $name Attribute Name
<<<<<<< HEAD
     * @param float|int|string $value Attribute Value
     *
     * @return $this
     */
    public function setAttribute(string $name, $value): static
=======
     * @param int|string $value Attribute Value
     *
     * @return $this
     */
    public function setAttribute($name, $value)
>>>>>>> main
    {
        $this->setEvaluatedFalse();
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Get AutoFilter Column Attributes.
     *
<<<<<<< HEAD
     * @return (float|int|string)[]
     */
    public function getAttributes(): array
=======
     * @return int[]|string[]
     */
    public function getAttributes()
>>>>>>> main
    {
        return $this->attributes;
    }

    /**
     * Get specific AutoFilter Column Attribute.
     *
     * @param string $name Attribute Name
<<<<<<< HEAD
     */
    public function getAttribute(string $name): null|float|int|string
=======
     *
     * @return null|int|string
     */
    public function getAttribute($name)
>>>>>>> main
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return null;
    }

    public function ruleCount(): int
    {
        return count($this->ruleset);
    }

    /**
     * Get all AutoFilter Column Rules.
     *
     * @return Column\Rule[]
     */
<<<<<<< HEAD
    public function getRules(): array
=======
    public function getRules()
>>>>>>> main
    {
        return $this->ruleset;
    }

    /**
     * Get a specified AutoFilter Column Rule.
     *
     * @param int $index Rule index in the ruleset array
<<<<<<< HEAD
     */
    public function getRule(int $index): Column\Rule
=======
     *
     * @return Column\Rule
     */
    public function getRule($index)
>>>>>>> main
    {
        if (!isset($this->ruleset[$index])) {
            $this->ruleset[$index] = new Column\Rule($this);
        }

        return $this->ruleset[$index];
    }

    /**
     * Create a new AutoFilter Column Rule in the ruleset.
<<<<<<< HEAD
     */
    public function createRule(): Column\Rule
=======
     *
     * @return Column\Rule
     */
    public function createRule()
>>>>>>> main
    {
        $this->setEvaluatedFalse();
        if ($this->filterType === self::AUTOFILTER_FILTERTYPE_CUSTOMFILTER && count($this->ruleset) >= 2) {
            throw new PhpSpreadsheetException('No more than 2 rules are allowed in a Custom Filter');
        }
        $this->ruleset[] = new Column\Rule($this);

        return end($this->ruleset);
    }

    /**
     * Add a new AutoFilter Column Rule to the ruleset.
     *
     * @return $this
     */
<<<<<<< HEAD
    public function addRule(Column\Rule $rule): static
=======
    public function addRule(Column\Rule $rule)
>>>>>>> main
    {
        $this->setEvaluatedFalse();
        $rule->setParent($this);
        $this->ruleset[] = $rule;

        return $this;
    }

    /**
     * Delete a specified AutoFilter Column Rule
     * If the number of rules is reduced to 1, then we reset And/Or logic to Or.
     *
     * @param int $index Rule index in the ruleset array
     *
     * @return $this
     */
<<<<<<< HEAD
    public function deleteRule(int $index): static
=======
    public function deleteRule($index)
>>>>>>> main
    {
        $this->setEvaluatedFalse();
        if (isset($this->ruleset[$index])) {
            unset($this->ruleset[$index]);
            //    If we've just deleted down to a single rule, then reset And/Or joining to Or
            if (count($this->ruleset) <= 1) {
                $this->setJoin(self::AUTOFILTER_COLUMN_JOIN_OR);
            }
        }

        return $this;
    }

    /**
     * Delete all AutoFilter Column Rules.
     *
     * @return $this
     */
<<<<<<< HEAD
    public function clearRules(): static
=======
    public function clearRules()
>>>>>>> main
    {
        $this->setEvaluatedFalse();
        $this->ruleset = [];
        $this->setJoin(self::AUTOFILTER_COLUMN_JOIN_OR);

        return $this;
    }

    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     */
    public function __clone()
    {
        $vars = get_object_vars($this);
<<<<<<< HEAD
        /** @var Column\Rule[] $value */
=======
>>>>>>> main
        foreach ($vars as $key => $value) {
            if ($key === 'parent') {
                // Detach from autofilter parent
                $this->parent = null;
            } elseif ($key === 'ruleset') {
                // The columns array of \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet\AutoFilter objects
                $this->ruleset = [];
                foreach ($value as $k => $v) {
                    $cloned = clone $v;
                    $cloned->setParent($this); // attach the new cloned Rule to this new cloned Autofilter Cloned object
                    $this->ruleset[$k] = $cloned;
                }
            } else {
                $this->$key = $value;
            }
        }
    }
}
