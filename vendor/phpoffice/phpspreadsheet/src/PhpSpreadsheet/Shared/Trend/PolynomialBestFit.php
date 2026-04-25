<?php

namespace PhpOffice\PhpSpreadsheet\Shared\Trend;

use Matrix\Matrix;
<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;
=======
>>>>>>> main

// Phpstan and Scrutinizer seem to have legitimate complaints.
// $this->slope is specified where an array is expected in several places.
// But it seems that it should always be float.
// This code is probably not exercised at all in unit tests.
<<<<<<< HEAD
// Private bool property $implemented is set to indicate
//     whether this implementation is correct.
=======
>>>>>>> main
class PolynomialBestFit extends BestFit
{
    /**
     * Algorithm type to use for best-fit
     * (Name of this Trend class).
<<<<<<< HEAD
     */
    protected string $bestFitType = 'polynomial';

    /**
     * Polynomial order.
     */
    protected int $order = 0;

    private bool $implemented = false;

    /**
     * Return the order of this polynomial.
     */
    public function getOrder(): int
=======
     *
     * @var string
     */
    protected $bestFitType = 'polynomial';

    /**
     * Polynomial order.
     *
     * @var int
     */
    protected $order = 0;

    /**
     * Return the order of this polynomial.
     *
     * @return int
     */
    public function getOrder()
>>>>>>> main
    {
        return $this->order;
    }

    /**
     * Return the Y-Value for a specified value of X.
     *
     * @param float $xValue X-Value
     *
     * @return float Y-Value
     */
<<<<<<< HEAD
    public function getValueOfYForX(float $xValue): float
=======
    public function getValueOfYForX($xValue)
>>>>>>> main
    {
        $retVal = $this->getIntersect();
        $slope = $this->getSlope();
        // Phpstan and Scrutinizer are both correct - getSlope returns float, not array.
        // @phpstan-ignore-next-line
        foreach ($slope as $key => $value) {
<<<<<<< HEAD
            /** @var float $value */
            if ($value != 0.0) {
                /** @var int $key */
=======
            if ($value != 0.0) {
>>>>>>> main
                $retVal += $value * $xValue ** ($key + 1);
            }
        }

        return $retVal;
    }

    /**
     * Return the X-Value for a specified value of Y.
     *
     * @param float $yValue Y-Value
     *
     * @return float X-Value
     */
<<<<<<< HEAD
    public function getValueOfXForY(float $yValue): float
=======
    public function getValueOfXForY($yValue)
>>>>>>> main
    {
        return ($yValue - $this->getIntersect()) / $this->getSlope();
    }

    /**
     * Return the Equation of the best-fit line.
     *
     * @param int $dp Number of places of decimal precision to display
<<<<<<< HEAD
     */
    public function getEquation(int $dp = 0): string
=======
     *
     * @return string
     */
    public function getEquation($dp = 0)
>>>>>>> main
    {
        $slope = $this->getSlope($dp);
        $intersect = $this->getIntersect($dp);

        $equation = 'Y = ' . $intersect;
        // Phpstan and Scrutinizer are both correct - getSlope returns float, not array.
        // @phpstan-ignore-next-line
        foreach ($slope as $key => $value) {
<<<<<<< HEAD
            /** @var float|int $value */
            if ($value != 0.0) {
                $equation .= ' + ' . $value . ' * X';
                /** @var int $key */
=======
            if ($value != 0.0) {
                $equation .= ' + ' . $value . ' * X';
>>>>>>> main
                if ($key > 0) {
                    $equation .= '^' . ($key + 1);
                }
            }
        }

        return $equation;
    }

    /**
     * Return the Slope of the line.
     *
     * @param int $dp Number of places of decimal precision to display
<<<<<<< HEAD
     */
    public function getSlope(int $dp = 0): float
    {
        if ($dp != 0) {
            $coefficients = [];
            //* @phpstan-ignore-next-line
            foreach ($this->slope as $coefficient) {
                /** @var float|int $coefficient */
=======
     *
     * @return float
     */
    public function getSlope($dp = 0)
    {
        if ($dp != 0) {
            $coefficients = [];
            // Scrutinizer is correct - $this->slope is float, not array.
            //* @phpstan-ignore-next-line
            foreach ($this->slope as $coefficient) {
>>>>>>> main
                $coefficients[] = round($coefficient, $dp);
            }

            // @phpstan-ignore-next-line
            return $coefficients;
        }

        return $this->slope;
    }

<<<<<<< HEAD
    /** @return array<float|int> */
    public function getCoefficients(int $dp = 0): array
=======
    /**
     * @param int $dp
     *
     * @return array
     */
    public function getCoefficients($dp = 0)
>>>>>>> main
    {
        // Phpstan and Scrutinizer are both correct - getSlope returns float, not array.
        // @phpstan-ignore-next-line
        return array_merge([$this->getIntersect($dp)], $this->getSlope($dp));
    }

    /**
     * Execute the regression and calculate the goodness of fit for a set of X and Y data values.
     *
     * @param int $order Order of Polynomial for this regression
     * @param float[] $yValues The set of Y-values for this regression
     * @param float[] $xValues The set of X-values for this regression
     */
<<<<<<< HEAD
    private function polynomialRegression(int $order, array $yValues, array $xValues): void
=======
    private function polynomialRegression($order, $yValues, $xValues): void
>>>>>>> main
    {
        // calculate sums
        $x_sum = array_sum($xValues);
        $y_sum = array_sum($yValues);
        $xx_sum = $xy_sum = $yy_sum = 0;
        for ($i = 0; $i < $this->valueCount; ++$i) {
            $xy_sum += $xValues[$i] * $yValues[$i];
            $xx_sum += $xValues[$i] * $xValues[$i];
            $yy_sum += $yValues[$i] * $yValues[$i];
        }
        /*
         *    This routine uses logic from the PHP port of polyfit version 0.1
         *    written by Michael Bommarito and Paul Meagher
         *
         *    The function fits a polynomial function of order $order through
         *    a series of x-y data points using least squares.
         *
         */
        $A = [];
        $B = [];
        for ($i = 0; $i < $this->valueCount; ++$i) {
            for ($j = 0; $j <= $order; ++$j) {
                $A[$i][$j] = $xValues[$i] ** $j;
            }
        }
        for ($i = 0; $i < $this->valueCount; ++$i) {
            $B[$i] = [$yValues[$i]];
        }
        $matrixA = new Matrix($A);
        $matrixB = new Matrix($B);
        $C = $matrixA->solve($matrixB);

        $coefficients = [];
        for ($i = 0; $i < $C->rows; ++$i) {
            $r = $C->getValue($i + 1, 1); // row and column are origin-1
<<<<<<< HEAD
            if (!is_numeric($r) || abs($r + 0) <= 10 ** (-9)) {
                $r = 0;
            } else {
                $r += 0;
=======
            if (abs($r) <= 10 ** (-9)) {
                $r = 0;
>>>>>>> main
            }
            $coefficients[] = $r;
        }

<<<<<<< HEAD
        $this->intersect = (float) array_shift($coefficients);
        // Phpstan is correct
=======
        $this->intersect = array_shift($coefficients);
        // Phpstan (and maybe Scrutinizer) are correct
>>>>>>> main
        //* @phpstan-ignore-next-line
        $this->slope = $coefficients;

        $this->calculateGoodnessOfFit($x_sum, $y_sum, $xx_sum, $yy_sum, $xy_sum, 0, 0, 0);
        foreach ($this->xValues as $xKey => $xValue) {
            $this->yBestFitValues[$xKey] = $this->getValueOfYForX($xValue);
        }
    }

    /**
     * Define the regression and calculate the goodness of fit for a set of X and Y data values.
     *
     * @param int $order Order of Polynomial for this regression
     * @param float[] $yValues The set of Y-values for this regression
     * @param float[] $xValues The set of X-values for this regression
     */
<<<<<<< HEAD
    public function __construct(int $order, array $yValues, array $xValues = [])
    {
        if (!$this->implemented) {
            throw new SpreadsheetException('Polynomial Best Fit not yet implemented');
        }

=======
    public function __construct($order, $yValues, $xValues = [])
    {
>>>>>>> main
        parent::__construct($yValues, $xValues);

        if (!$this->error) {
            if ($order < $this->valueCount) {
                $this->bestFitType .= '_' . $order;
                $this->order = $order;
                $this->polynomialRegression($order, $yValues, $xValues);
                if (($this->getGoodnessOfFit() < 0.0) || ($this->getGoodnessOfFit() > 1.0)) {
                    $this->error = true;
                }
            } else {
                $this->error = true;
            }
        }
    }
}
