<?php

namespace PhpOffice\PhpSpreadsheet\Chart;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlotArea
{
    /**
     * No fill in plot area (show Excel gridlines through chart).
<<<<<<< HEAD
     */
    private bool $noFill = false;
=======
     *
     * @var bool
     */
    private $noFill = false;
>>>>>>> main

    /**
     * PlotArea Gradient Stop list.
     * Each entry is a 2-element array.
     *     First is position in %.
     *     Second is ChartColor.
     *
<<<<<<< HEAD
     * @var array<array{float, ChartColor}>
     */
    private array $gradientFillStops = [];

    /**
     * PlotArea Gradient Angle.
     */
    private ?float $gradientFillAngle = null;

    /**
     * PlotArea Layout.
     */
    private ?Layout $layout;
=======
     * @var array[]
     */
    private $gradientFillStops = [];

    /**
     * PlotArea Gradient Angle.
     *
     * @var ?float
     */
    private $gradientFillAngle;

    /**
     * PlotArea Layout.
     *
     * @var ?Layout
     */
    private $layout;
>>>>>>> main

    /**
     * Plot Series.
     *
     * @var DataSeries[]
     */
<<<<<<< HEAD
    private array $plotSeries;
=======
    private $plotSeries = [];
>>>>>>> main

    /**
     * Create a new PlotArea.
     *
     * @param DataSeries[] $plotSeries
     */
    public function __construct(?Layout $layout = null, array $plotSeries = [])
    {
        $this->layout = $layout;
        $this->plotSeries = $plotSeries;
    }

    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    /**
     * Get Number of Plot Groups.
     */
    public function getPlotGroupCount(): int
    {
        return count($this->plotSeries);
    }

    /**
     * Get Number of Plot Series.
<<<<<<< HEAD
     */
    public function getPlotSeriesCount(): int|float
=======
     *
     * @return int
     */
    public function getPlotSeriesCount()
>>>>>>> main
    {
        $seriesCount = 0;
        foreach ($this->plotSeries as $plot) {
            $seriesCount += $plot->getPlotSeriesCount();
        }

        return $seriesCount;
    }

    /**
     * Get Plot Series.
     *
     * @return DataSeries[]
     */
<<<<<<< HEAD
    public function getPlotGroup(): array
=======
    public function getPlotGroup()
>>>>>>> main
    {
        return $this->plotSeries;
    }

    /**
     * Get Plot Series by Index.
<<<<<<< HEAD
     */
    public function getPlotGroupByIndex(int $index): DataSeries
=======
     *
     * @param mixed $index
     *
     * @return DataSeries
     */
    public function getPlotGroupByIndex($index)
>>>>>>> main
    {
        return $this->plotSeries[$index];
    }

    /**
     * Set Plot Series.
     *
     * @param DataSeries[] $plotSeries
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setPlotSeries(array $plotSeries): static
=======
    public function setPlotSeries(array $plotSeries)
>>>>>>> main
    {
        $this->plotSeries = $plotSeries;

        return $this;
    }

    public function refresh(Worksheet $worksheet): void
    {
        foreach ($this->plotSeries as $plotSeries) {
            $plotSeries->refresh($worksheet);
        }
    }

    public function setNoFill(bool $noFill): self
    {
        $this->noFill = $noFill;

        return $this;
    }

    public function getNoFill(): bool
    {
        return $this->noFill;
    }

<<<<<<< HEAD
    /** @param array<array{float, ChartColor}> $gradientFillStops */
=======
>>>>>>> main
    public function setGradientFillProperties(array $gradientFillStops, ?float $gradientFillAngle): self
    {
        $this->gradientFillStops = $gradientFillStops;
        $this->gradientFillAngle = $gradientFillAngle;

        return $this;
    }

    /**
     * Get gradientFillAngle.
     */
    public function getGradientFillAngle(): ?float
    {
        return $this->gradientFillAngle;
    }

    /**
     * Get gradientFillStops.
     *
<<<<<<< HEAD
     * @return array<array{float, ChartColor}>
     */
    public function getGradientFillStops(): array
=======
     * @return array
     */
    public function getGradientFillStops()
>>>>>>> main
    {
        return $this->gradientFillStops;
    }

<<<<<<< HEAD
    private ?int $gapWidth = null;

    private bool $useUpBars = false;

    private bool $useDownBars = false;
=======
    /** @var ?int */
    private $gapWidth;

    /** @var bool */
    private $useUpBars = false;

    /** @var bool */
    private $useDownBars = false;
>>>>>>> main

    public function getGapWidth(): ?int
    {
        return $this->gapWidth;
    }

    public function setGapWidth(?int $gapWidth): self
    {
        $this->gapWidth = $gapWidth;

        return $this;
    }

    public function getUseUpBars(): bool
    {
        return $this->useUpBars;
    }

    public function setUseUpBars(bool $useUpBars): self
    {
        $this->useUpBars = $useUpBars;

        return $this;
    }

    public function getUseDownBars(): bool
    {
        return $this->useDownBars;
    }

    public function setUseDownBars(bool $useDownBars): self
    {
        $this->useDownBars = $useDownBars;

        return $this;
    }
<<<<<<< HEAD

    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     */
    public function __clone()
    {
        $this->layout = ($this->layout === null) ? null : clone $this->layout;
        $plotSeries = $this->plotSeries;
        $this->plotSeries = [];
        foreach ($plotSeries as $series) {
            $this->plotSeries[] = clone $series;
        }
    }
=======
>>>>>>> main
}
