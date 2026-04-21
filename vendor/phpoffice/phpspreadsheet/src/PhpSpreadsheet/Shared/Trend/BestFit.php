<?php

namespace PhpOffice\PhpSpreadsheet\Shared\Trend;

abstract class BestFit
{
    /**
     * Indicator flag for a calculation error.
<<<<<<< HEAD
     */
    protected bool $error = false;

    /**
     * Algorithm type to use for best-fit.
     */
    protected string $bestFitType = 'undetermined';

    /**
     * Number of entries in the sets of x- and y-value arrays.
     */
    protected int $valueCount;
=======
     *
     * @var bool
     */
    protected $error = false;

    /**
     * Algorithm type to use for best-fit.
     *
     * @var string
     */
    protected $bestFitType = 'undetermined';

    /**
     * Number of entries in the sets of x- and y-value arrays.
     *
     * @var int
     */
    protected $valueCount = 0;
>>>>>>> main

    /**
     * X-value dataseries of values.
     *
     * @var float[]
     */
<<<<<<< HEAD
    protected array $xValues = [];
=======
    protected $xValues = [];
>>>>>>> main

    /**
     * Y-value dataseries of values.
     *
     * @var float[]
     */
<<<<<<< HEAD
    protected array $yValues = [];

    /**
     * Flag indicating whether values should be adjusted to Y=0.
     */
    protected bool $adjustToZero = false;
=======
    protected $yValues = [];

    /**
     * Flag indicating whether values should be adjusted to Y=0.
     *
     * @var bool
     */
    protected $adjustToZero = false;
>>>>>>> main

    /**
     * Y-value series of best-fit values.
     *
     * @var float[]
     */
<<<<<<< HEAD
    protected array $yBestFitValues = [];

    protected float $goodnessOfFit = 1;

    protected float $stdevOfResiduals = 0;

    protected float $covariance = 0;

    protected float $correlation = 0;

    protected float $SSRegression = 0;

    protected float $SSResiduals = 0;

    protected float $DFResiduals = 0;

    protected float $f = 0;

    protected float $slope = 0;

    protected float $slopeSE = 0;

    protected float $intersect = 0;

    protected float $intersectSE = 0;

    protected float $xOffset = 0;

    protected float $yOffset = 0;

    public function getError(): bool
=======
    protected $yBestFitValues = [];

    /** @var float */
    protected $goodnessOfFit = 1;

    /** @var float */
    protected $stdevOfResiduals = 0;

    /** @var float */
    protected $covariance = 0;

    /** @var float */
    protected $correlation = 0;

    /** @var float */
    protected $SSRegression = 0;

    /** @var float */
    protected $SSResiduals = 0;

    /** @var float */
    protected $DFResiduals = 0;

    /** @var float */
    protected $f = 0;

    /** @var float */
    protected $slope = 0;

    /** @var float */
    protected $slopeSE = 0;

    /** @var float */
    protected $intersect = 0;

    /** @var float */
    protected $intersectSE = 0;

    /** @var float */
    protected $xOffset = 0;

    /** @var float */
    protected $yOffset = 0;

    /** @return bool */
    public function getError()
>>>>>>> main
    {
        return $this->error;
    }

<<<<<<< HEAD
    public function getBestFitType(): string
=======
    /** @return string */
    public function getBestFitType()
>>>>>>> main
    {
        return $this->bestFitType;
    }

    /**
     * Return the Y-Value for a specified value of X.
     *
     * @param float $xValue X-Value
     *
     * @return float Y-Value
     */
<<<<<<< HEAD
    abstract public function getValueOfYForX(float $xValue): float;
=======
    abstract public function getValueOfYForX($xValue);
>>>>>>> main

    /**
     * Return the X-Value for a specified value of Y.
     *
     * @param float $yValue Y-Value
     *
     * @return float X-Value
     */
<<<<<<< HEAD
    abstract public function getValueOfXForY(float $yValue): float;
=======
    abstract public function getValueOfXForY($yValue);
>>>>>>> main

    /**
     * Return the original set of X-Values.
     *
     * @return float[] X-Values
     */
<<<<<<< HEAD
    public function getXValues(): array
=======
    public function getXValues()
>>>>>>> main
    {
        return $this->xValues;
    }

    /**
<<<<<<< HEAD
     * Return the original set of Y-Values.
     *
     * @return float[] Y-Values
     */
    public function getYValues(): array
    {
        return $this->yValues;
    }

    /**
     * Return the Equation of the best-fit line.
     *
     * @param int $dp Number of places of decimal precision to display
     */
    abstract public function getEquation(int $dp = 0): string;
=======
     * Return the Equation of the best-fit line.
     *
     * @param int $dp Number of places of decimal precision to display
     *
     * @return string
     */
    abstract public function getEquation($dp = 0);
>>>>>>> main

    /**
     * Return the Slope of the line.
     *
     * @param int $dp Number of places of decimal precision to display
<<<<<<< HEAD
     */
    public function getSlope(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getSlope($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->slope, $dp);
        }

        return $this->slope;
    }

    /**
     * Return the standard error of the Slope.
     *
     * @param int $dp Number of places of decimal precision to display
<<<<<<< HEAD
     */
    public function getSlopeSE(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getSlopeSE($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->slopeSE, $dp);
        }

        return $this->slopeSE;
    }

    /**
     * Return the Value of X where it intersects Y = 0.
     *
     * @param int $dp Number of places of decimal precision to display
<<<<<<< HEAD
     */
    public function getIntersect(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getIntersect($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->intersect, $dp);
        }

        return $this->intersect;
    }

    /**
     * Return the standard error of the Intersect.
     *
     * @param int $dp Number of places of decimal precision to display
<<<<<<< HEAD
     */
    public function getIntersectSE(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getIntersectSE($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->intersectSE, $dp);
        }

        return $this->intersectSE;
    }

    /**
     * Return the goodness of fit for this regression.
     *
     * @param int $dp Number of places of decimal precision to return
<<<<<<< HEAD
     */
    public function getGoodnessOfFit(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getGoodnessOfFit($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->goodnessOfFit, $dp);
        }

        return $this->goodnessOfFit;
    }

    /**
     * Return the goodness of fit for this regression.
     *
     * @param int $dp Number of places of decimal precision to return
<<<<<<< HEAD
     */
    public function getGoodnessOfFitPercent(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getGoodnessOfFitPercent($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->goodnessOfFit * 100, $dp);
        }

        return $this->goodnessOfFit * 100;
    }

    /**
     * Return the standard deviation of the residuals for this regression.
     *
     * @param int $dp Number of places of decimal precision to return
<<<<<<< HEAD
     */
    public function getStdevOfResiduals(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getStdevOfResiduals($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->stdevOfResiduals, $dp);
        }

        return $this->stdevOfResiduals;
    }

    /**
     * @param int $dp Number of places of decimal precision to return
<<<<<<< HEAD
     */
    public function getSSRegression(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getSSRegression($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->SSRegression, $dp);
        }

        return $this->SSRegression;
    }

    /**
     * @param int $dp Number of places of decimal precision to return
<<<<<<< HEAD
     */
    public function getSSResiduals(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getSSResiduals($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->SSResiduals, $dp);
        }

        return $this->SSResiduals;
    }

    /**
     * @param int $dp Number of places of decimal precision to return
<<<<<<< HEAD
     */
    public function getDFResiduals(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getDFResiduals($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->DFResiduals, $dp);
        }

        return $this->DFResiduals;
    }

    /**
     * @param int $dp Number of places of decimal precision to return
<<<<<<< HEAD
     */
    public function getF(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getF($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->f, $dp);
        }

        return $this->f;
    }

    /**
     * @param int $dp Number of places of decimal precision to return
<<<<<<< HEAD
     */
    public function getCovariance(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getCovariance($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->covariance, $dp);
        }

        return $this->covariance;
    }

    /**
     * @param int $dp Number of places of decimal precision to return
<<<<<<< HEAD
     */
    public function getCorrelation(int $dp = 0): float
=======
     *
     * @return float
     */
    public function getCorrelation($dp = 0)
>>>>>>> main
    {
        if ($dp != 0) {
            return round($this->correlation, $dp);
        }

        return $this->correlation;
    }

    /**
     * @return float[]
     */
<<<<<<< HEAD
    public function getYBestFitValues(): array
=======
    public function getYBestFitValues()
>>>>>>> main
    {
        return $this->yBestFitValues;
    }

<<<<<<< HEAD
    protected function calculateGoodnessOfFit(float $sumX, float $sumY, float $sumX2, float $sumY2, float $sumXY, float $meanX, float $meanY, bool|int $const): void
=======
    /** @var mixed */
    private static $scrutinizerZeroPointZero = 0.0;

    /**
     * @param mixed $x
     * @param mixed $y
     */
    private static function scrutinizerLooseCompare($x, $y): bool
    {
        return $x == $y;
    }

    /**
     * @param float $sumX
     * @param float $sumY
     * @param float $sumX2
     * @param float $sumY2
     * @param float $sumXY
     * @param float $meanX
     * @param float $meanY
     * @param bool|int $const
     */
    protected function calculateGoodnessOfFit($sumX, $sumY, $sumX2, $sumY2, $sumXY, $meanX, $meanY, $const): void
>>>>>>> main
    {
        $SSres = $SScov = $SStot = $SSsex = 0.0;
        foreach ($this->xValues as $xKey => $xValue) {
            $bestFitY = $this->yBestFitValues[$xKey] = $this->getValueOfYForX($xValue);

            $SSres += ($this->yValues[$xKey] - $bestFitY) * ($this->yValues[$xKey] - $bestFitY);
            if ($const === true) {
                $SStot += ($this->yValues[$xKey] - $meanY) * ($this->yValues[$xKey] - $meanY);
            } else {
                $SStot += $this->yValues[$xKey] * $this->yValues[$xKey];
            }
            $SScov += ($this->xValues[$xKey] - $meanX) * ($this->yValues[$xKey] - $meanY);
            if ($const === true) {
                $SSsex += ($this->xValues[$xKey] - $meanX) * ($this->xValues[$xKey] - $meanX);
            } else {
                $SSsex += $this->xValues[$xKey] * $this->xValues[$xKey];
            }
        }

        $this->SSResiduals = $SSres;
        $this->DFResiduals = $this->valueCount - 1 - ($const === true ? 1 : 0);

        if ($this->DFResiduals == 0.0) {
            $this->stdevOfResiduals = 0.0;
        } else {
            $this->stdevOfResiduals = sqrt($SSres / $this->DFResiduals);
        }
<<<<<<< HEAD

        if ($SStot == 0.0 || $SSres == $SStot) {
=======
        // Scrutinizer thinks $SSres == $SStot is always true. It is wrong.
        if ($SStot == self::$scrutinizerZeroPointZero || self::scrutinizerLooseCompare($SSres, $SStot)) {
>>>>>>> main
            $this->goodnessOfFit = 1;
        } else {
            $this->goodnessOfFit = 1 - ($SSres / $SStot);
        }

        $this->SSRegression = $this->goodnessOfFit * $SStot;
        $this->covariance = $SScov / $this->valueCount;
        $this->correlation = ($this->valueCount * $sumXY - $sumX * $sumY) / sqrt(($this->valueCount * $sumX2 - $sumX ** 2) * ($this->valueCount * $sumY2 - $sumY ** 2));
        $this->slopeSE = $this->stdevOfResiduals / sqrt($SSsex);
        $this->intersectSE = $this->stdevOfResiduals * sqrt(1 / ($this->valueCount - ($sumX * $sumX) / $sumX2));
        if ($this->SSResiduals != 0.0) {
            if ($this->DFResiduals == 0.0) {
                $this->f = 0.0;
            } else {
                $this->f = $this->SSRegression / ($this->SSResiduals / $this->DFResiduals);
            }
        } else {
            if ($this->DFResiduals == 0.0) {
                $this->f = 0.0;
            } else {
                $this->f = $this->SSRegression / $this->DFResiduals;
            }
        }
    }

<<<<<<< HEAD
    /**
     * @param array<float|int> $values
     *
     * @return float|int
     */
=======
    /** @return float|int */
>>>>>>> main
    private function sumSquares(array $values)
    {
        return array_sum(
            array_map(
<<<<<<< HEAD
                fn ($value): float|int => $value ** 2,
=======
                function ($value) {
                    return $value ** 2;
                },
>>>>>>> main
                $values
            )
        );
    }

    /**
     * @param float[] $yValues
     * @param float[] $xValues
     */
    protected function leastSquareFit(array $yValues, array $xValues, bool $const): void
    {
        // calculate sums
        $sumValuesX = array_sum($xValues);
        $sumValuesY = array_sum($yValues);
        $meanValueX = $sumValuesX / $this->valueCount;
        $meanValueY = $sumValuesY / $this->valueCount;
        $sumSquaresX = $this->sumSquares($xValues);
        $sumSquaresY = $this->sumSquares($yValues);
        $mBase = $mDivisor = 0.0;
        $xy_sum = 0.0;
        for ($i = 0; $i < $this->valueCount; ++$i) {
            $xy_sum += $xValues[$i] * $yValues[$i];

            if ($const === true) {
                $mBase += ($xValues[$i] - $meanValueX) * ($yValues[$i] - $meanValueY);
                $mDivisor += ($xValues[$i] - $meanValueX) * ($xValues[$i] - $meanValueX);
            } else {
                $mBase += $xValues[$i] * $yValues[$i];
                $mDivisor += $xValues[$i] * $xValues[$i];
            }
        }

        // calculate slope
        $this->slope = $mBase / $mDivisor;

        // calculate intersect
        $this->intersect = ($const === true) ? $meanValueY - ($this->slope * $meanValueX) : 0.0;

        $this->calculateGoodnessOfFit($sumValuesX, $sumValuesY, $sumSquaresX, $sumSquaresY, $xy_sum, $meanValueX, $meanValueY, $const);
    }

    /**
     * Define the regression.
     *
     * @param float[] $yValues The set of Y-values for this regression
     * @param float[] $xValues The set of X-values for this regression
     */
<<<<<<< HEAD
    public function __construct(array $yValues, array $xValues = [])
=======
    public function __construct($yValues, $xValues = [])
>>>>>>> main
    {
        //    Calculate number of points
        $yValueCount = count($yValues);
        $xValueCount = count($xValues);

        //    Define X Values if necessary
        if ($xValueCount === 0) {
<<<<<<< HEAD
            $xValues = range(1.0, $yValueCount);
=======
            $xValues = range(1, $yValueCount);
>>>>>>> main
        } elseif ($yValueCount !== $xValueCount) {
            //    Ensure both arrays of points are the same size
            $this->error = true;
        }

        $this->valueCount = $yValueCount;
        $this->xValues = $xValues;
        $this->yValues = $yValues;
    }
}
