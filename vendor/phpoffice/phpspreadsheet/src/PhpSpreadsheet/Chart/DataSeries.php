<?php

namespace PhpOffice\PhpSpreadsheet\Chart;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataSeries
{
    const TYPE_BARCHART = 'barChart';
    const TYPE_BARCHART_3D = 'bar3DChart';
    const TYPE_LINECHART = 'lineChart';
    const TYPE_LINECHART_3D = 'line3DChart';
    const TYPE_AREACHART = 'areaChart';
    const TYPE_AREACHART_3D = 'area3DChart';
    const TYPE_PIECHART = 'pieChart';
    const TYPE_PIECHART_3D = 'pie3DChart';
    const TYPE_DOUGHNUTCHART = 'doughnutChart';
    const TYPE_DONUTCHART = self::TYPE_DOUGHNUTCHART; // Synonym
    const TYPE_SCATTERCHART = 'scatterChart';
    const TYPE_SURFACECHART = 'surfaceChart';
    const TYPE_SURFACECHART_3D = 'surface3DChart';
    const TYPE_RADARCHART = 'radarChart';
    const TYPE_BUBBLECHART = 'bubbleChart';
    const TYPE_STOCKCHART = 'stockChart';
    const TYPE_CANDLECHART = self::TYPE_STOCKCHART; // Synonym

    const GROUPING_CLUSTERED = 'clustered';
    const GROUPING_STACKED = 'stacked';
    const GROUPING_PERCENT_STACKED = 'percentStacked';
    const GROUPING_STANDARD = 'standard';

    const DIRECTION_BAR = 'bar';
    const DIRECTION_HORIZONTAL = self::DIRECTION_BAR;
    const DIRECTION_COL = 'col';
    const DIRECTION_COLUMN = self::DIRECTION_COL;
    const DIRECTION_VERTICAL = self::DIRECTION_COL;

    const STYLE_LINEMARKER = 'lineMarker';
    const STYLE_SMOOTHMARKER = 'smoothMarker';
    const STYLE_MARKER = 'marker';
    const STYLE_FILLED = 'filled';

    const EMPTY_AS_GAP = 'gap';
    const EMPTY_AS_ZERO = 'zero';
    const EMPTY_AS_SPAN = 'span';
<<<<<<< HEAD
    const DEFAULT_EMPTY_AS = self::EMPTY_AS_GAP;
    const VALID_EMPTY_AS = [self::EMPTY_AS_GAP, self::EMPTY_AS_ZERO, self::EMPTY_AS_SPAN];

    /**
     * Series Plot Type.
     */
    private ?string $plotType;

    /**
     * Plot Grouping Type.
     */
    private ?string $plotGrouping;

    /**
     * Plot Direction.
     */
    private string $plotDirection;

    /**
     * Plot Style.
     */
    private ?string $plotStyle;
=======

    /**
     * Series Plot Type.
     *
     * @var string
     */
    private $plotType;

    /**
     * Plot Grouping Type.
     *
     * @var string
     */
    private $plotGrouping;

    /**
     * Plot Direction.
     *
     * @var string
     */
    private $plotDirection;

    /**
     * Plot Style.
     *
     * @var null|string
     */
    private $plotStyle;
>>>>>>> main

    /**
     * Order of plots in Series.
     *
     * @var int[]
     */
<<<<<<< HEAD
    private array $plotOrder;
=======
    private $plotOrder = [];
>>>>>>> main

    /**
     * Plot Label.
     *
     * @var DataSeriesValues[]
     */
<<<<<<< HEAD
    private array $plotLabel;
=======
    private $plotLabel = [];
>>>>>>> main

    /**
     * Plot Category.
     *
     * @var DataSeriesValues[]
     */
<<<<<<< HEAD
    private array $plotCategory;

    /**
     * Smooth Line. Must be specified for both DataSeries and DataSeriesValues.
     */
    private bool $smoothLine;
=======
    private $plotCategory = [];

    /**
     * Smooth Line. Must be specified for both DataSeries and DataSeriesValues.
     *
     * @var bool
     */
    private $smoothLine;
>>>>>>> main

    /**
     * Plot Values.
     *
     * @var DataSeriesValues[]
     */
<<<<<<< HEAD
    private array $plotValues;
=======
    private $plotValues = [];
>>>>>>> main

    /**
     * Plot Bubble Sizes.
     *
     * @var DataSeriesValues[]
     */
<<<<<<< HEAD
    private array $plotBubbleSizes = [];
=======
    private $plotBubbleSizes = [];
>>>>>>> main

    /**
     * Create a new DataSeries.
     *
<<<<<<< HEAD
=======
     * @param null|mixed $plotType
     * @param null|mixed $plotGrouping
>>>>>>> main
     * @param int[] $plotOrder
     * @param DataSeriesValues[] $plotLabel
     * @param DataSeriesValues[] $plotCategory
     * @param DataSeriesValues[] $plotValues
<<<<<<< HEAD
     */
    public function __construct(
        null|string $plotType = null,
        null|string $plotGrouping = null,
        array $plotOrder = [],
        array $plotLabel = [],
        array $plotCategory = [],
        array $plotValues = [],
        ?string $plotDirection = null,
        bool $smoothLine = false,
        ?string $plotStyle = null
    ) {
=======
     * @param null|string $plotDirection
     * @param bool $smoothLine
     * @param null|string $plotStyle
     */
    public function __construct($plotType = null, $plotGrouping = null, array $plotOrder = [], array $plotLabel = [], array $plotCategory = [], array $plotValues = [], $plotDirection = null, $smoothLine = false, $plotStyle = null)
    {
>>>>>>> main
        $this->plotType = $plotType;
        $this->plotGrouping = $plotGrouping;
        $this->plotOrder = $plotOrder;
        $keys = array_keys($plotValues);
        $this->plotValues = $plotValues;
        if (!isset($plotLabel[$keys[0]])) {
            $plotLabel[$keys[0]] = new DataSeriesValues();
        }
        $this->plotLabel = $plotLabel;

        if (!isset($plotCategory[$keys[0]])) {
            $plotCategory[$keys[0]] = new DataSeriesValues();
        }
        $this->plotCategory = $plotCategory;

<<<<<<< HEAD
        $this->smoothLine = (bool) $smoothLine;
=======
        $this->smoothLine = $smoothLine;
>>>>>>> main
        $this->plotStyle = $plotStyle;

        if ($plotDirection === null) {
            $plotDirection = self::DIRECTION_COL;
        }
        $this->plotDirection = $plotDirection;
    }

    /**
     * Get Plot Type.
<<<<<<< HEAD
     */
    public function getPlotType(): ?string
=======
     *
     * @return string
     */
    public function getPlotType()
>>>>>>> main
    {
        return $this->plotType;
    }

    /**
     * Set Plot Type.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setPlotType(string $plotType): static
=======
     * @param string $plotType
     *
     * @return $this
     */
    public function setPlotType($plotType)
>>>>>>> main
    {
        $this->plotType = $plotType;

        return $this;
    }

    /**
     * Get Plot Grouping Type.
<<<<<<< HEAD
     */
    public function getPlotGrouping(): ?string
=======
     *
     * @return string
     */
    public function getPlotGrouping()
>>>>>>> main
    {
        return $this->plotGrouping;
    }

    /**
     * Set Plot Grouping Type.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setPlotGrouping(string $groupingType): static
=======
     * @param string $groupingType
     *
     * @return $this
     */
    public function setPlotGrouping($groupingType)
>>>>>>> main
    {
        $this->plotGrouping = $groupingType;

        return $this;
    }

    /**
     * Get Plot Direction.
<<<<<<< HEAD
     */
    public function getPlotDirection(): string
=======
     *
     * @return string
     */
    public function getPlotDirection()
>>>>>>> main
    {
        return $this->plotDirection;
    }

    /**
     * Set Plot Direction.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setPlotDirection(string $plotDirection): static
=======
     * @param string $plotDirection
     *
     * @return $this
     */
    public function setPlotDirection($plotDirection)
>>>>>>> main
    {
        $this->plotDirection = $plotDirection;

        return $this;
    }

    /**
     * Get Plot Order.
     *
     * @return int[]
     */
<<<<<<< HEAD
    public function getPlotOrder(): array
=======
    public function getPlotOrder()
>>>>>>> main
    {
        return $this->plotOrder;
    }

    /**
     * Get Plot Labels.
     *
     * @return DataSeriesValues[]
     */
<<<<<<< HEAD
    public function getPlotLabels(): array
=======
    public function getPlotLabels()
>>>>>>> main
    {
        return $this->plotLabel;
    }

    /**
     * Get Plot Label by Index.
     *
<<<<<<< HEAD
     * @return DataSeriesValues|false
     */
    public function getPlotLabelByIndex(int $index): bool|DataSeriesValues
=======
     * @param mixed $index
     *
     * @return DataSeriesValues|false
     */
    public function getPlotLabelByIndex($index)
>>>>>>> main
    {
        $keys = array_keys($this->plotLabel);
        if (in_array($index, $keys)) {
            return $this->plotLabel[$index];
        }

        return false;
    }

    /**
     * Get Plot Categories.
     *
     * @return DataSeriesValues[]
     */
<<<<<<< HEAD
    public function getPlotCategories(): array
=======
    public function getPlotCategories()
>>>>>>> main
    {
        return $this->plotCategory;
    }

    /**
     * Get Plot Category by Index.
     *
<<<<<<< HEAD
     * @return DataSeriesValues|false
     */
    public function getPlotCategoryByIndex(int $index): bool|DataSeriesValues
=======
     * @param mixed $index
     *
     * @return DataSeriesValues|false
     */
    public function getPlotCategoryByIndex($index)
>>>>>>> main
    {
        $keys = array_keys($this->plotCategory);
        if (in_array($index, $keys)) {
            return $this->plotCategory[$index];
        } elseif (isset($keys[$index])) {
            return $this->plotCategory[$keys[$index]];
        }

        return false;
    }

    /**
     * Get Plot Style.
<<<<<<< HEAD
     */
    public function getPlotStyle(): ?string
=======
     *
     * @return null|string
     */
    public function getPlotStyle()
>>>>>>> main
    {
        return $this->plotStyle;
    }

    /**
     * Set Plot Style.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setPlotStyle(?string $plotStyle): static
=======
     * @param null|string $plotStyle
     *
     * @return $this
     */
    public function setPlotStyle($plotStyle)
>>>>>>> main
    {
        $this->plotStyle = $plotStyle;

        return $this;
    }

    /**
     * Get Plot Values.
     *
     * @return DataSeriesValues[]
     */
<<<<<<< HEAD
    public function getPlotValues(): array
=======
    public function getPlotValues()
>>>>>>> main
    {
        return $this->plotValues;
    }

    /**
     * Get Plot Values by Index.
     *
<<<<<<< HEAD
     * @return DataSeriesValues|false
     */
    public function getPlotValuesByIndex(int $index): bool|DataSeriesValues
=======
     * @param mixed $index
     *
     * @return DataSeriesValues|false
     */
    public function getPlotValuesByIndex($index)
>>>>>>> main
    {
        $keys = array_keys($this->plotValues);
        if (in_array($index, $keys)) {
            return $this->plotValues[$index];
        }

        return false;
    }

    /**
     * Get Plot Bubble Sizes.
     *
     * @return DataSeriesValues[]
     */
    public function getPlotBubbleSizes(): array
    {
        return $this->plotBubbleSizes;
    }

    /**
     * Set Plot Bubble Sizes.
     *
     * @param DataSeriesValues[] $plotBubbleSizes
     */
    public function setPlotBubbleSizes(array $plotBubbleSizes): self
    {
        $this->plotBubbleSizes = $plotBubbleSizes;

        return $this;
    }

    /**
     * Get Number of Plot Series.
<<<<<<< HEAD
     */
    public function getPlotSeriesCount(): int
=======
     *
     * @return int
     */
    public function getPlotSeriesCount()
>>>>>>> main
    {
        return count($this->plotValues);
    }

    /**
     * Get Smooth Line.
<<<<<<< HEAD
     */
    public function getSmoothLine(): bool
=======
     *
     * @return bool
     */
    public function getSmoothLine()
>>>>>>> main
    {
        return $this->smoothLine;
    }

    /**
     * Set Smooth Line.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setSmoothLine(bool $smoothLine): static
=======
     * @param bool $smoothLine
     *
     * @return $this
     */
    public function setSmoothLine($smoothLine)
>>>>>>> main
    {
        $this->smoothLine = $smoothLine;

        return $this;
    }

    public function refresh(Worksheet $worksheet): void
    {
        foreach ($this->plotValues as $plotValues) {
<<<<<<< HEAD
            $plotValues->refresh($worksheet, true);
        }
        foreach ($this->plotLabel as $plotValues) {
            $plotValues->refresh($worksheet, true);
        }
        foreach ($this->plotCategory as $plotValues) {
            $plotValues->refresh($worksheet, false);
        }
    }

    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     */
    public function __clone()
    {
        $plotLabels = $this->plotLabel;
        $this->plotLabel = [];
        foreach ($plotLabels as $plotLabel) {
            $this->plotLabel[] = $plotLabel;
        }
        $plotCategories = $this->plotCategory;
        $this->plotCategory = [];
        foreach ($plotCategories as $plotCategory) {
            $this->plotCategory[] = clone $plotCategory;
        }
        $plotValues = $this->plotValues;
        $this->plotValues = [];
        foreach ($plotValues as $plotValue) {
            $this->plotValues[] = clone $plotValue;
        }
        $plotBubbleSizes = $this->plotBubbleSizes;
        $this->plotBubbleSizes = [];
        foreach ($plotBubbleSizes as $plotBubbleSize) {
            $this->plotBubbleSizes[] = clone $plotBubbleSize;
=======
            if ($plotValues !== null) {
                $plotValues->refresh($worksheet, true);
            }
        }
        foreach ($this->plotLabel as $plotValues) {
            if ($plotValues !== null) {
                $plotValues->refresh($worksheet, true);
            }
        }
        foreach ($this->plotCategory as $plotValues) {
            if ($plotValues !== null) {
                $plotValues->refresh($worksheet, false);
            }
>>>>>>> main
        }
    }
}
