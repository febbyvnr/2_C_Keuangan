<?php

namespace PhpOffice\PhpSpreadsheet\Chart;

/**
 * Created by PhpStorm.
 * User: Wiktor Trzonkowski
 * Date: 6/17/14
 * Time: 12:11 PM.
 */
class Axis extends Properties
{
    const AXIS_TYPE_CATEGORY = 'catAx';
    const AXIS_TYPE_DATE = 'dateAx';
    const AXIS_TYPE_VALUE = 'valAx';

    const TIME_UNIT_DAYS = 'days';
    const TIME_UNIT_MONTHS = 'months';
    const TIME_UNIT_YEARS = 'years';

    public function __construct()
    {
        parent::__construct();
        $this->fillColor = new ChartColor();
    }

    /**
     * Chart Major Gridlines as.
<<<<<<< HEAD
     */
    private ?GridLines $majorGridlines = null;

    /**
     * Chart Minor Gridlines as.
     */
    private ?GridLines $minorGridlines = null;
=======
     *
     * @var ?GridLines
     */
    private $majorGridlines;

    /**
     * Chart Minor Gridlines as.
     *
     * @var ?GridLines
     */
    private $minorGridlines;
>>>>>>> main

    /**
     * Axis Number.
     *
<<<<<<< HEAD
     * @var array{format: string, source_linked: int, numeric: ?bool}
     */
    private array $axisNumber = [
=======
     * @var mixed[]
     */
    private $axisNumber = [
>>>>>>> main
        'format' => self::FORMAT_CODE_GENERAL,
        'source_linked' => 1,
        'numeric' => null,
    ];

<<<<<<< HEAD
    private string $axisType = '';

    private ?AxisText $axisText = null;

    private ?Title $dispUnitsTitle = null;
=======
    /** @var string */
    private $axisType = '';

    /** @var ?AxisText */
    private $axisText;
>>>>>>> main

    /**
     * Axis Options.
     *
<<<<<<< HEAD
     * @var array<string, null|string>
     */
    private array $axisOptions = [
=======
     * @var mixed[]
     */
    private $axisOptions = [
>>>>>>> main
        'minimum' => null,
        'maximum' => null,
        'major_unit' => null,
        'minor_unit' => null,
        'orientation' => self::ORIENTATION_NORMAL,
        'minor_tick_mark' => self::TICK_MARK_NONE,
        'major_tick_mark' => self::TICK_MARK_NONE,
        'axis_labels' => self::AXIS_LABELS_NEXT_TO,
        'horizontal_crosses' => self::HORIZONTAL_CROSSES_AUTOZERO,
        'horizontal_crosses_value' => null,
        'textRotation' => null,
        'hidden' => null,
        'majorTimeUnit' => self::TIME_UNIT_YEARS,
        'minorTimeUnit' => self::TIME_UNIT_MONTHS,
        'baseTimeUnit' => self::TIME_UNIT_DAYS,
<<<<<<< HEAD
        'logBase' => null,
        'dispUnitsBuiltIn' => null,
    ];
    public const DISP_UNITS_HUNDREDS = 'hundreds';
    public const DISP_UNITS_THOUSANDS = 'thousands';
    public const DISP_UNITS_TEN_THOUSANDS = 'tenThousands';
    public const DISP_UNITS_HUNDRED_THOUSANDS = 'hundredThousands';
    public const DISP_UNITS_MILLIONS = 'millions';
    public const DISP_UNITS_TEN_MILLIONS = 'tenMillions';
    public const DISP_UNITS_HUNDRED_MILLIONS = 'hundredMillions';
    public const DISP_UNITS_BILLIONS = 'billions';
    public const DISP_UNITS_TRILLIONS = 'trillions';
    public const TRILLION_INDEX = (PHP_INT_SIZE > 4) ? 1000000000000 : '1000000000000';
    public const DISP_UNITS_BUILTIN_INT = [
        100 => self::DISP_UNITS_HUNDREDS,
        1000 => self::DISP_UNITS_THOUSANDS,
        10000 => self::DISP_UNITS_TEN_THOUSANDS,
        100000 => self::DISP_UNITS_HUNDRED_THOUSANDS,
        1000000 => self::DISP_UNITS_MILLIONS,
        10000000 => self::DISP_UNITS_TEN_MILLIONS,
        100000000 => self::DISP_UNITS_HUNDRED_MILLIONS,
        1000000000 => self::DISP_UNITS_BILLIONS,
        self::TRILLION_INDEX => self::DISP_UNITS_TRILLIONS, // overflow for 32-bit
=======
>>>>>>> main
    ];

    /**
     * Fill Properties.
<<<<<<< HEAD
     */
    private ChartColor $fillColor;
=======
     *
     * @var ChartColor
     */
    private $fillColor;
>>>>>>> main

    private const NUMERIC_FORMAT = [
        Properties::FORMAT_CODE_NUMBER,
        Properties::FORMAT_CODE_DATE,
        Properties::FORMAT_CODE_DATE_ISO8601,
    ];

<<<<<<< HEAD
    private bool $noFill = false;

    /**
     * Get Series Data Type.
     */
    public function setAxisNumberProperties(string $format_code, ?bool $numeric = null, int $sourceLinked = 0): void
    {
        $format = $format_code;
=======
    /** @var bool */
    private $noFill = false;

    /**
     * Get Series Data Type.
     *
     * @param mixed $format_code
     */
    public function setAxisNumberProperties($format_code, ?bool $numeric = null, int $sourceLinked = 0): void
    {
        $format = (string) $format_code;
>>>>>>> main
        $this->axisNumber['format'] = $format;
        $this->axisNumber['source_linked'] = $sourceLinked;
        if (is_bool($numeric)) {
            $this->axisNumber['numeric'] = $numeric;
        } elseif (in_array($format, self::NUMERIC_FORMAT, true)) {
            $this->axisNumber['numeric'] = true;
        }
    }

    /**
     * Get Axis Number Format Data Type.
<<<<<<< HEAD
     */
    public function getAxisNumberFormat(): string
=======
     *
     * @return string
     */
    public function getAxisNumberFormat()
>>>>>>> main
    {
        return $this->axisNumber['format'];
    }

    /**
     * Get Axis Number Source Linked.
<<<<<<< HEAD
     */
    public function getAxisNumberSourceLinked(): string
=======
     *
     * @return string
     */
    public function getAxisNumberSourceLinked()
>>>>>>> main
    {
        return (string) $this->axisNumber['source_linked'];
    }

    public function getAxisIsNumericFormat(): bool
    {
        return $this->axisType === self::AXIS_TYPE_DATE || (bool) $this->axisNumber['numeric'];
    }

<<<<<<< HEAD
    public function setAxisOption(string $key, null|float|int|string $value): void
    {
        if ($value !== null && $value !== '') {
            $this->axisOptions[$key] = (string) $value;
=======
    public function setAxisOption(string $key, ?string $value): void
    {
        if ($value !== null && $value !== '') {
            $this->axisOptions[$key] = $value;
>>>>>>> main
        }
    }

    /**
     * Set Axis Options Properties.
     */
    public function setAxisOptionsProperties(
        string $axisLabels,
        ?string $horizontalCrossesValue = null,
        ?string $horizontalCrosses = null,
        ?string $axisOrientation = null,
        ?string $majorTmt = null,
        ?string $minorTmt = null,
<<<<<<< HEAD
        null|float|int|string $minimum = null,
        null|float|int|string $maximum = null,
        null|float|int|string $majorUnit = null,
        null|float|int|string $minorUnit = null,
        null|float|int|string $textRotation = null,
        ?string $hidden = null,
        ?string $baseTimeUnit = null,
        ?string $majorTimeUnit = null,
        ?string $minorTimeUnit = null,
        null|float|int|string $logBase = null,
        ?string $dispUnitsBuiltIn = null
=======
        ?string $minimum = null,
        ?string $maximum = null,
        ?string $majorUnit = null,
        ?string $minorUnit = null,
        ?string $textRotation = null,
        ?string $hidden = null,
        ?string $baseTimeUnit = null,
        ?string $majorTimeUnit = null,
        ?string $minorTimeUnit = null
>>>>>>> main
    ): void {
        $this->axisOptions['axis_labels'] = $axisLabels;
        $this->setAxisOption('horizontal_crosses_value', $horizontalCrossesValue);
        $this->setAxisOption('horizontal_crosses', $horizontalCrosses);
        $this->setAxisOption('orientation', $axisOrientation);
        $this->setAxisOption('major_tick_mark', $majorTmt);
        $this->setAxisOption('minor_tick_mark', $minorTmt);
        $this->setAxisOption('minimum', $minimum);
        $this->setAxisOption('maximum', $maximum);
        $this->setAxisOption('major_unit', $majorUnit);
        $this->setAxisOption('minor_unit', $minorUnit);
        $this->setAxisOption('textRotation', $textRotation);
        $this->setAxisOption('hidden', $hidden);
        $this->setAxisOption('baseTimeUnit', $baseTimeUnit);
        $this->setAxisOption('majorTimeUnit', $majorTimeUnit);
        $this->setAxisOption('minorTimeUnit', $minorTimeUnit);
<<<<<<< HEAD
        $this->setAxisOption('logBase', $logBase);
        $this->setAxisOption('dispUnitsBuiltIn', $dispUnitsBuiltIn);
=======
>>>>>>> main
    }

    /**
     * Get Axis Options Property.
<<<<<<< HEAD
     */
    public function getAxisOptionsProperty(string $property): ?string
=======
     *
     * @param string $property
     *
     * @return ?string
     */
    public function getAxisOptionsProperty($property)
>>>>>>> main
    {
        if ($property === 'textRotation') {
            if ($this->axisText !== null) {
                if ($this->axisText->getRotation() !== null) {
                    return (string) $this->axisText->getRotation();
                }
            }
        }

        return $this->axisOptions[$property];
    }

    /**
     * Set Axis Orientation Property.
<<<<<<< HEAD
     */
    public function setAxisOrientation(string $orientation): void
=======
     *
     * @param string $orientation
     */
    public function setAxisOrientation($orientation): void
>>>>>>> main
    {
        $this->axisOptions['orientation'] = (string) $orientation;
    }

    public function getAxisType(): string
    {
        return $this->axisType;
    }

    public function setAxisType(string $type): self
    {
        if ($type === self::AXIS_TYPE_CATEGORY || $type === self::AXIS_TYPE_VALUE || $type === self::AXIS_TYPE_DATE) {
            $this->axisType = $type;
        } else {
            $this->axisType = '';
        }

        return $this;
    }

    /**
     * Set Fill Property.
<<<<<<< HEAD
     */
    public function setFillParameters(?string $color, ?int $alpha = null, ?string $AlphaType = ChartColor::EXCEL_COLOR_TYPE_RGB): void
=======
     *
     * @param ?string $color
     * @param ?int $alpha
     * @param ?string $AlphaType
     */
    public function setFillParameters($color, $alpha = null, $AlphaType = ChartColor::EXCEL_COLOR_TYPE_RGB): void
>>>>>>> main
    {
        $this->fillColor->setColorProperties($color, $alpha, $AlphaType);
    }

    /**
     * Get Fill Property.
<<<<<<< HEAD
     */
    public function getFillProperty(string $property): string
=======
     *
     * @param string $property
     *
     * @return string
     */
    public function getFillProperty($property)
>>>>>>> main
    {
        return (string) $this->fillColor->getColorProperty($property);
    }

    public function getFillColorObject(): ChartColor
    {
        return $this->fillColor;
    }

<<<<<<< HEAD
    private string $crossBetween = ''; // 'between' or 'midCat' might be better
=======
    /**
     * Get Line Color Property.
     *
     * @deprecated 1.24.0
     *      Use the getLineColor property in the Properties class instead
     * @see Properties::getLineColorProperty()
     *
     * @param string $propertyName
     *
     * @return null|int|string
     */
    public function getLineProperty($propertyName)
    {
        return $this->getLineColorProperty($propertyName);
    }

    /** @var string */
    private $crossBetween = ''; // 'between' or 'midCat' might be better
>>>>>>> main

    public function setCrossBetween(string $crossBetween): self
    {
        $this->crossBetween = $crossBetween;

        return $this;
    }

    public function getCrossBetween(): string
    {
        return $this->crossBetween;
    }

    public function getMajorGridlines(): ?GridLines
    {
        return $this->majorGridlines;
    }

    public function getMinorGridlines(): ?GridLines
    {
        return $this->minorGridlines;
    }

    public function setMajorGridlines(?GridLines $gridlines): self
    {
        $this->majorGridlines = $gridlines;

        return $this;
    }

    public function setMinorGridlines(?GridLines $gridlines): self
    {
        $this->minorGridlines = $gridlines;

        return $this;
    }

    public function getAxisText(): ?AxisText
    {
        return $this->axisText;
    }

    public function setAxisText(?AxisText $axisText): self
    {
        $this->axisText = $axisText;

        return $this;
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

    public function setDispUnitsTitle(?Title $dispUnitsTitle): self
    {
        $this->dispUnitsTitle = $dispUnitsTitle;

        return $this;
    }

    public function getDispUnitsTitle(): ?Title
    {
        return $this->dispUnitsTitle;
    }

    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     */
    public function __clone()
    {
        parent::__clone();
        $this->majorGridlines = ($this->majorGridlines === null) ? null : clone $this->majorGridlines;
        $this->majorGridlines = ($this->minorGridlines === null) ? null : clone $this->minorGridlines;
        $this->axisText = ($this->axisText === null) ? null : clone $this->axisText;
        $this->dispUnitsTitle = ($this->dispUnitsTitle === null) ? null : clone $this->dispUnitsTitle;
        $this->fillColor = clone $this->fillColor;
    }
=======
>>>>>>> main
}
