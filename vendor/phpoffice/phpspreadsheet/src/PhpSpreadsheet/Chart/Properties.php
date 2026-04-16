<?php

namespace PhpOffice\PhpSpreadsheet\Chart;

/**
 * Created by PhpStorm.
 * User: nhw2h8s
 * Date: 7/2/14
 * Time: 5:45 PM.
 */
abstract class Properties
{
<<<<<<< HEAD
    const AXIS_LABELS_LOW = 'low';
=======
    /** @deprecated 1.24 use constant from ChartColor instead */
    const EXCEL_COLOR_TYPE_STANDARD = ChartColor::EXCEL_COLOR_TYPE_STANDARD;
    /** @deprecated 1.24 use constant from ChartColor instead */
    const EXCEL_COLOR_TYPE_SCHEME = ChartColor::EXCEL_COLOR_TYPE_SCHEME;
    /** @deprecated 1.24 use constant from ChartColor instead */
    const EXCEL_COLOR_TYPE_ARGB = ChartColor::EXCEL_COLOR_TYPE_ARGB;

    const
        AXIS_LABELS_LOW = 'low';
>>>>>>> main
    const AXIS_LABELS_HIGH = 'high';
    const AXIS_LABELS_NEXT_TO = 'nextTo';
    const AXIS_LABELS_NONE = 'none';

<<<<<<< HEAD
    const TICK_MARK_NONE = 'none';
=======
    const
        TICK_MARK_NONE = 'none';
>>>>>>> main
    const TICK_MARK_INSIDE = 'in';
    const TICK_MARK_OUTSIDE = 'out';
    const TICK_MARK_CROSS = 'cross';

<<<<<<< HEAD
    const HORIZONTAL_CROSSES_AUTOZERO = 'autoZero';
    const HORIZONTAL_CROSSES_MAXIMUM = 'max';

    const FORMAT_CODE_GENERAL = 'General';
=======
    const
        HORIZONTAL_CROSSES_AUTOZERO = 'autoZero';
    const HORIZONTAL_CROSSES_MAXIMUM = 'max';

    const
        FORMAT_CODE_GENERAL = 'General';
>>>>>>> main
    const FORMAT_CODE_NUMBER = '#,##0.00';
    const FORMAT_CODE_CURRENCY = '$#,##0.00';
    const FORMAT_CODE_ACCOUNTING = '_($* #,##0.00_);_($* (#,##0.00);_($* "-"??_);_(@_)';
    const FORMAT_CODE_DATE = 'm/d/yyyy';
    const FORMAT_CODE_DATE_ISO8601 = 'yyyy-mm-dd';
    const FORMAT_CODE_TIME = '[$-F400]h:mm:ss AM/PM';
    const FORMAT_CODE_PERCENTAGE = '0.00%';
    const FORMAT_CODE_FRACTION = '# ?/?';
    const FORMAT_CODE_SCIENTIFIC = '0.00E+00';
    const FORMAT_CODE_TEXT = '@';
    const FORMAT_CODE_SPECIAL = '00000';

<<<<<<< HEAD
    const ORIENTATION_NORMAL = 'minMax';
    const ORIENTATION_REVERSED = 'maxMin';

    const LINE_STYLE_COMPOUND_SIMPLE = 'sng';
=======
    const
        ORIENTATION_NORMAL = 'minMax';
    const ORIENTATION_REVERSED = 'maxMin';

    const
        LINE_STYLE_COMPOUND_SIMPLE = 'sng';
>>>>>>> main
    const LINE_STYLE_COMPOUND_DOUBLE = 'dbl';
    const LINE_STYLE_COMPOUND_THICKTHIN = 'thickThin';
    const LINE_STYLE_COMPOUND_THINTHICK = 'thinThick';
    const LINE_STYLE_COMPOUND_TRIPLE = 'tri';
    const LINE_STYLE_DASH_SOLID = 'solid';
    const LINE_STYLE_DASH_ROUND_DOT = 'sysDot';
    const LINE_STYLE_DASH_SQUARE_DOT = 'sysDash';
<<<<<<< HEAD
=======
    /** @deprecated 1.24 use LINE_STYLE_DASH_SQUARE_DOT instead */
    const LINE_STYLE_DASH_SQUERE_DOT = 'sysDash';
>>>>>>> main
    const LINE_STYPE_DASH_DASH = 'dash';
    const LINE_STYLE_DASH_DASH_DOT = 'dashDot';
    const LINE_STYLE_DASH_LONG_DASH = 'lgDash';
    const LINE_STYLE_DASH_LONG_DASH_DOT = 'lgDashDot';
    const LINE_STYLE_DASH_LONG_DASH_DOT_DOT = 'lgDashDotDot';
    const LINE_STYLE_CAP_SQUARE = 'sq';
    const LINE_STYLE_CAP_ROUND = 'rnd';
    const LINE_STYLE_CAP_FLAT = 'flat';
    const LINE_STYLE_JOIN_ROUND = 'round';
    const LINE_STYLE_JOIN_MITER = 'miter';
    const LINE_STYLE_JOIN_BEVEL = 'bevel';
    const LINE_STYLE_ARROW_TYPE_NOARROW = null;
    const LINE_STYLE_ARROW_TYPE_ARROW = 'triangle';
    const LINE_STYLE_ARROW_TYPE_OPEN = 'arrow';
    const LINE_STYLE_ARROW_TYPE_STEALTH = 'stealth';
    const LINE_STYLE_ARROW_TYPE_DIAMOND = 'diamond';
    const LINE_STYLE_ARROW_TYPE_OVAL = 'oval';
    const LINE_STYLE_ARROW_SIZE_1 = 1;
    const LINE_STYLE_ARROW_SIZE_2 = 2;
    const LINE_STYLE_ARROW_SIZE_3 = 3;
    const LINE_STYLE_ARROW_SIZE_4 = 4;
    const LINE_STYLE_ARROW_SIZE_5 = 5;
    const LINE_STYLE_ARROW_SIZE_6 = 6;
    const LINE_STYLE_ARROW_SIZE_7 = 7;
    const LINE_STYLE_ARROW_SIZE_8 = 8;
    const LINE_STYLE_ARROW_SIZE_9 = 9;

<<<<<<< HEAD
    const SHADOW_PRESETS_NOSHADOW = null;
=======
    const
        SHADOW_PRESETS_NOSHADOW = null;
>>>>>>> main
    const SHADOW_PRESETS_OUTER_BOTTTOM_RIGHT = 1;
    const SHADOW_PRESETS_OUTER_BOTTOM = 2;
    const SHADOW_PRESETS_OUTER_BOTTOM_LEFT = 3;
    const SHADOW_PRESETS_OUTER_RIGHT = 4;
    const SHADOW_PRESETS_OUTER_CENTER = 5;
    const SHADOW_PRESETS_OUTER_LEFT = 6;
    const SHADOW_PRESETS_OUTER_TOP_RIGHT = 7;
    const SHADOW_PRESETS_OUTER_TOP = 8;
    const SHADOW_PRESETS_OUTER_TOP_LEFT = 9;
    const SHADOW_PRESETS_INNER_BOTTTOM_RIGHT = 10;
    const SHADOW_PRESETS_INNER_BOTTOM = 11;
    const SHADOW_PRESETS_INNER_BOTTOM_LEFT = 12;
    const SHADOW_PRESETS_INNER_RIGHT = 13;
    const SHADOW_PRESETS_INNER_CENTER = 14;
    const SHADOW_PRESETS_INNER_LEFT = 15;
    const SHADOW_PRESETS_INNER_TOP_RIGHT = 16;
    const SHADOW_PRESETS_INNER_TOP = 17;
    const SHADOW_PRESETS_INNER_TOP_LEFT = 18;
    const SHADOW_PRESETS_PERSPECTIVE_BELOW = 19;
    const SHADOW_PRESETS_PERSPECTIVE_UPPER_RIGHT = 20;
    const SHADOW_PRESETS_PERSPECTIVE_UPPER_LEFT = 21;
    const SHADOW_PRESETS_PERSPECTIVE_LOWER_RIGHT = 22;
    const SHADOW_PRESETS_PERSPECTIVE_LOWER_LEFT = 23;

<<<<<<< HEAD
    const POINTS_WIDTH_MULTIPLIER = 12_700;
    const ANGLE_MULTIPLIER = 60_000; // direction and size-kx size-ky
    const PERCENTAGE_MULTIPLIER = 100_000; // size sx and sy, and gradient pos
    const MAX_SKEW_ANGLE_XML = 90 * self::ANGLE_MULTIPLIER - 1;
    const MAX_SKEW_ANGLE_DEGREES = self::MAX_SKEW_ANGLE_XML / self::ANGLE_MULTIPLIER; // max for size-kx size-ky

    protected bool $objectState = false; // used only for minor gridlines

    protected ?float $glowSize = null;

    protected ChartColor $glowColor;

    /** @var array{size: ?float} */
    protected array $softEdges = [
        'size' => null,
    ];

    /** @var mixed[] */
    protected array $shadowProperties = self::PRESETS_OPTIONS[0];

    protected ChartColor $shadowColor;
=======
    const POINTS_WIDTH_MULTIPLIER = 12700;
    const ANGLE_MULTIPLIER = 60000; // direction and size-kx size-ky
    const PERCENTAGE_MULTIPLIER = 100000; // size sx and sy

    /** @var bool */
    protected $objectState = false; // used only for minor gridlines

    /** @var ?float */
    protected $glowSize;

    /** @var ChartColor */
    protected $glowColor;

    /** @var array */
    protected $softEdges = [
        'size' => null,
    ];

    /** @var array */
    protected $shadowProperties = self::PRESETS_OPTIONS[0];

    /** @var ChartColor */
    protected $shadowColor;
>>>>>>> main

    public function __construct()
    {
        $this->lineColor = new ChartColor();
        $this->glowColor = new ChartColor();
        $this->shadowColor = new ChartColor();
        $this->shadowColor->setType(ChartColor::EXCEL_COLOR_TYPE_STANDARD);
        $this->shadowColor->setValue('black');
        $this->shadowColor->setAlpha(40);
    }

    /**
     * Get Object State.
<<<<<<< HEAD
     */
    public function getObjectState(): bool
=======
     *
     * @return bool
     */
    public function getObjectState()
>>>>>>> main
    {
        return $this->objectState;
    }

    /**
     * Change Object State to True.
     *
     * @return $this
     */
    public function activateObject()
    {
        $this->objectState = true;

        return $this;
    }

    public static function pointsToXml(float $width): string
    {
        return (string) (int) ($width * self::POINTS_WIDTH_MULTIPLIER);
    }

    public static function xmlToPoints(string $width): float
    {
        return ((float) $width) / self::POINTS_WIDTH_MULTIPLIER;
    }

    public static function angleToXml(float $angle): string
    {
        return (string) (int) ($angle * self::ANGLE_MULTIPLIER);
    }

    public static function xmlToAngle(string $angle): float
    {
        return ((float) $angle) / self::ANGLE_MULTIPLIER;
    }

    public static function tenthOfPercentToXml(float $value): string
    {
        return (string) (int) ($value * self::PERCENTAGE_MULTIPLIER);
    }

    public static function xmlToTenthOfPercent(string $value): float
    {
        return ((float) $value) / self::PERCENTAGE_MULTIPLIER;
    }

<<<<<<< HEAD
    /** @return array{type: ?string, value: ?string, alpha: ?int} */
    protected function setColorProperties(?string $color, null|float|int|string $alpha, ?string $colorType): array
=======
    /**
     * @param null|float|int|string $alpha
     */
    protected function setColorProperties(?string $color, $alpha, ?string $colorType): array
>>>>>>> main
    {
        return [
            'type' => $colorType,
            'value' => $color,
            'alpha' => ($alpha === null) ? null : (int) $alpha,
        ];
    }

    protected const PRESETS_OPTIONS = [
        //NONE
        0 => [
            'presets' => self::SHADOW_PRESETS_NOSHADOW,
            'effect' => null,
            //'color' => [
            //    'type' => ChartColor::EXCEL_COLOR_TYPE_STANDARD,
            //    'value' => 'black',
            //    'alpha' => 40,
            //],
            'size' => [
                'sx' => null,
                'sy' => null,
                'kx' => null,
                'ky' => null,
            ],
            'blur' => null,
            'direction' => null,
            'distance' => null,
            'algn' => null,
            'rotWithShape' => null,
        ],
        //OUTER
        1 => [
            'effect' => 'outerShdw',
            'blur' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 38100 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 2700000 / self::ANGLE_MULTIPLIER,
            'algn' => 'tl',
            'rotWithShape' => '0',
        ],
        2 => [
            'effect' => 'outerShdw',
            'blur' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 38100 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 5400000 / self::ANGLE_MULTIPLIER,
            'algn' => 't',
            'rotWithShape' => '0',
        ],
        3 => [
            'effect' => 'outerShdw',
            'blur' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 38100 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 8100000 / self::ANGLE_MULTIPLIER,
            'algn' => 'tr',
            'rotWithShape' => '0',
        ],
        4 => [
            'effect' => 'outerShdw',
            'blur' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 38100 / self::POINTS_WIDTH_MULTIPLIER,
            'algn' => 'l',
            'rotWithShape' => '0',
        ],
        5 => [
            'effect' => 'outerShdw',
            'size' => [
                'sx' => 102000 / self::PERCENTAGE_MULTIPLIER,
                'sy' => 102000 / self::PERCENTAGE_MULTIPLIER,
            ],
            'blur' => 63500 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 38100 / self::POINTS_WIDTH_MULTIPLIER,
            'algn' => 'ctr',
            'rotWithShape' => '0',
        ],
        6 => [
            'effect' => 'outerShdw',
            'blur' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 38100 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 10800000 / self::ANGLE_MULTIPLIER,
            'algn' => 'r',
            'rotWithShape' => '0',
        ],
        7 => [
            'effect' => 'outerShdw',
            'blur' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 38100 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 18900000 / self::ANGLE_MULTIPLIER,
            'algn' => 'bl',
            'rotWithShape' => '0',
        ],
        8 => [
            'effect' => 'outerShdw',
            'blur' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 38100 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 16200000 / self::ANGLE_MULTIPLIER,
            'rotWithShape' => '0',
        ],
        9 => [
            'effect' => 'outerShdw',
            'blur' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 38100 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 13500000 / self::ANGLE_MULTIPLIER,
            'algn' => 'br',
            'rotWithShape' => '0',
        ],
        //INNER
        10 => [
            'effect' => 'innerShdw',
            'blur' => 63500 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 2700000 / self::ANGLE_MULTIPLIER,
        ],
        11 => [
            'effect' => 'innerShdw',
            'blur' => 63500 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 5400000 / self::ANGLE_MULTIPLIER,
        ],
        12 => [
            'effect' => 'innerShdw',
            'blur' => 63500 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 8100000 / self::ANGLE_MULTIPLIER,
        ],
        13 => [
            'effect' => 'innerShdw',
            'blur' => 63500 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
        ],
        14 => [
            'effect' => 'innerShdw',
            'blur' => 114300 / self::POINTS_WIDTH_MULTIPLIER,
        ],
        15 => [
            'effect' => 'innerShdw',
            'blur' => 63500 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 10800000 / self::ANGLE_MULTIPLIER,
        ],
        16 => [
            'effect' => 'innerShdw',
            'blur' => 63500 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 18900000 / self::ANGLE_MULTIPLIER,
        ],
        17 => [
            'effect' => 'innerShdw',
            'blur' => 63500 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 16200000 / self::ANGLE_MULTIPLIER,
        ],
        18 => [
            'effect' => 'innerShdw',
            'blur' => 63500 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 50800 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 13500000 / self::ANGLE_MULTIPLIER,
        ],
        //perspective
        19 => [
            'effect' => 'outerShdw',
            'blur' => 152400 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 317500 / self::POINTS_WIDTH_MULTIPLIER,
            'size' => [
                'sx' => 90000 / self::PERCENTAGE_MULTIPLIER,
                'sy' => -19000 / self::PERCENTAGE_MULTIPLIER,
            ],
            'direction' => 5400000 / self::ANGLE_MULTIPLIER,
            'rotWithShape' => '0',
        ],
        20 => [
            'effect' => 'outerShdw',
            'blur' => 76200 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 18900000 / self::ANGLE_MULTIPLIER,
            'size' => [
                'sy' => 23000 / self::PERCENTAGE_MULTIPLIER,
                'kx' => -1200000 / self::ANGLE_MULTIPLIER,
            ],
            'algn' => 'bl',
            'rotWithShape' => '0',
        ],
        21 => [
            'effect' => 'outerShdw',
            'blur' => 76200 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 13500000 / self::ANGLE_MULTIPLIER,
            'size' => [
                'sy' => 23000 / self::PERCENTAGE_MULTIPLIER,
                'kx' => 1200000 / self::ANGLE_MULTIPLIER,
            ],
            'algn' => 'br',
            'rotWithShape' => '0',
        ],
        22 => [
            'effect' => 'outerShdw',
            'blur' => 76200 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 12700 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 2700000 / self::ANGLE_MULTIPLIER,
            'size' => [
                'sy' => -23000 / self::PERCENTAGE_MULTIPLIER,
                'kx' => -800400 / self::ANGLE_MULTIPLIER,
            ],
            'algn' => 'bl',
            'rotWithShape' => '0',
        ],
        23 => [
            'effect' => 'outerShdw',
            'blur' => 76200 / self::POINTS_WIDTH_MULTIPLIER,
            'distance' => 12700 / self::POINTS_WIDTH_MULTIPLIER,
            'direction' => 8100000 / self::ANGLE_MULTIPLIER,
            'size' => [
                'sy' => -23000 / self::PERCENTAGE_MULTIPLIER,
                'kx' => 800400 / self::ANGLE_MULTIPLIER,
            ],
            'algn' => 'br',
            'rotWithShape' => '0',
        ],
    ];

<<<<<<< HEAD
    /** @return mixed[] */
=======
>>>>>>> main
    protected function getShadowPresetsMap(int $presetsOption): array
    {
        return self::PRESETS_OPTIONS[$presetsOption] ?? self::PRESETS_OPTIONS[0];
    }

    /**
     * Get value of array element.
     *
<<<<<<< HEAD
     * @param mixed[] $properties
     * @param array<mixed>|int|string $elements
     */
    protected function getArrayElementsValue(array $properties, array|int|string $elements): mixed
=======
     * @param mixed $properties
     * @param mixed $elements
     *
     * @return mixed
     */
    protected function getArrayElementsValue($properties, $elements)
>>>>>>> main
    {
        $reference = &$properties;
        if (!is_array($elements)) {
            return $reference[$elements];
        }

        foreach ($elements as $keys) {
<<<<<<< HEAD
            $reference = &$reference[$keys]; //* @phpstan-ignore-line
=======
            $reference = &$reference[$keys];
>>>>>>> main
        }

        return $reference;
    }

    /**
     * Set Glow Properties.
<<<<<<< HEAD
     */
    public function setGlowProperties(float $size, ?string $colorValue = null, ?int $colorAlpha = null, ?string $colorType = null): void
=======
     *
     * @param float $size
     * @param ?string $colorValue
     * @param ?int $colorAlpha
     * @param ?string $colorType
     */
    public function setGlowProperties($size, $colorValue = null, $colorAlpha = null, $colorType = null): void
>>>>>>> main
    {
        $this
            ->activateObject()
            ->setGlowSize($size);
        $this->glowColor->setColorPropertiesArray(
            [
                'value' => $colorValue,
                'type' => $colorType,
                'alpha' => $colorAlpha,
            ]
        );
    }

    /**
     * Get Glow Property.
     *
<<<<<<< HEAD
     * @param mixed[]|string $property
     *
     * @return null|array<mixed>|float|int|string
     */
    public function getGlowProperty(array|string $property): null|array|float|int|string
=======
     * @param array|string $property
     *
     * @return null|array|float|int|string
     */
    public function getGlowProperty($property)
>>>>>>> main
    {
        $retVal = null;
        if ($property === 'size') {
            $retVal = $this->glowSize;
        } elseif ($property === 'color') {
            $retVal = [
                'value' => $this->glowColor->getColorProperty('value'),
                'type' => $this->glowColor->getColorProperty('type'),
                'alpha' => $this->glowColor->getColorProperty('alpha'),
            ];
        } elseif (is_array($property) && count($property) >= 2 && $property[0] === 'color') {
<<<<<<< HEAD
            /** @var string */
            $temp = $property[1];
            $retVal = $this->glowColor->getColorProperty($temp);
=======
            $retVal = $this->glowColor->getColorProperty($property[1]);
>>>>>>> main
        }

        return $retVal;
    }

    /**
     * Get Glow Color Property.
<<<<<<< HEAD
     */
    public function getGlowColor(string $propertyName): null|int|string
=======
     *
     * @param string $propertyName
     *
     * @return null|int|string
     */
    public function getGlowColor($propertyName)
>>>>>>> main
    {
        return $this->glowColor->getColorProperty($propertyName);
    }

    public function getGlowColorObject(): ChartColor
    {
        return $this->glowColor;
    }

    /**
     * Get Glow Size.
<<<<<<< HEAD
     */
    public function getGlowSize(): ?float
=======
     *
     * @return ?float
     */
    public function getGlowSize()
>>>>>>> main
    {
        return $this->glowSize;
    }

    /**
     * Set Glow Size.
     *
<<<<<<< HEAD
     * @return $this
     */
    protected function setGlowSize(?float $size)
=======
     * @param ?float $size
     *
     * @return $this
     */
    protected function setGlowSize($size)
>>>>>>> main
    {
        $this->glowSize = $size;

        return $this;
    }

    /**
     * Set Soft Edges Size.
<<<<<<< HEAD
     */
    public function setSoftEdges(?float $size): void
=======
     *
     * @param ?float $size
     */
    public function setSoftEdges($size): void
>>>>>>> main
    {
        if ($size !== null) {
            $this->activateObject();
            $this->softEdges['size'] = $size;
        }
    }

    /**
     * Get Soft Edges Size.
<<<<<<< HEAD
     */
    public function getSoftEdgesSize(): ?float
=======
     *
     * @return string
     */
    public function getSoftEdgesSize()
>>>>>>> main
    {
        return $this->softEdges['size'];
    }

<<<<<<< HEAD
    /** @param null|array{value?: ?string, alpha?: null|int|string, brightness?: null|int|string, type?: ?string}|float|string  $value */
    public function setShadowProperty(string $propertyName, mixed $value): self
    {
        $this->activateObject();
        if ($propertyName === 'color' && is_array($value)) {
            /** @var array{value: ?string, alpha: null|int|string, brightness?: null|int|string, type: ?string} */
            $valuex = $value;
            $this->shadowColor->setColorPropertiesArray($valuex);
=======
    /**
     * @param mixed $value
     */
    public function setShadowProperty(string $propertyName, $value): self
    {
        $this->activateObject();
        if ($propertyName === 'color' && is_array($value)) {
            $this->shadowColor->setColorPropertiesArray($value);
>>>>>>> main
        } else {
            $this->shadowProperties[$propertyName] = $value;
        }

        return $this;
    }

    /**
     * Set Shadow Properties.
<<<<<<< HEAD
     */
    public function setShadowProperties(int $presets, ?string $colorValue = null, ?string $colorType = null, null|float|int|string $colorAlpha = null, ?float $blur = null, ?int $angle = null, ?float $distance = null): void
=======
     *
     * @param int $presets
     * @param string $colorValue
     * @param string $colorType
     * @param null|float|int|string $colorAlpha
     * @param null|float $blur
     * @param null|int $angle
     * @param null|float $distance
     */
    public function setShadowProperties($presets, $colorValue = null, $colorType = null, $colorAlpha = null, $blur = null, $angle = null, $distance = null): void
>>>>>>> main
    {
        $this->activateObject()->setShadowPresetsProperties((int) $presets);
        if ($presets === 0) {
            $this->shadowColor->setType(ChartColor::EXCEL_COLOR_TYPE_STANDARD);
            $this->shadowColor->setValue('black');
            $this->shadowColor->setAlpha(40);
        }
        if ($colorValue !== null) {
            $this->shadowColor->setValue($colorValue);
        }
        if ($colorType !== null) {
            $this->shadowColor->setType($colorType);
        }
        if (is_numeric($colorAlpha)) {
            $this->shadowColor->setAlpha((int) $colorAlpha);
        }
        $this
            ->setShadowBlur($blur)
            ->setShadowAngle($angle)
            ->setShadowDistance($distance);
    }

    /**
     * Set Shadow Presets Properties.
     *
<<<<<<< HEAD
     * @return $this
     */
    protected function setShadowPresetsProperties(int $presets)
=======
     * @param int $presets
     *
     * @return $this
     */
    protected function setShadowPresetsProperties($presets)
>>>>>>> main
    {
        $this->shadowProperties['presets'] = $presets;
        $this->setShadowPropertiesMapValues($this->getShadowPresetsMap($presets));

        return $this;
    }

    protected const SHADOW_ARRAY_KEYS = ['size', 'color'];

    /**
     * Set Shadow Properties Values.
     *
<<<<<<< HEAD
     * @param mixed[] $propertiesMap
     * @param null|mixed[] $reference
     *
     * @return $this
     */
    protected function setShadowPropertiesMapValues(array $propertiesMap, ?array &$reference = null)
=======
     * @param mixed $reference
     *
     * @return $this
     */
    protected function setShadowPropertiesMapValues(array $propertiesMap, &$reference = null)
>>>>>>> main
    {
        $base_reference = $reference;
        foreach ($propertiesMap as $property_key => $property_val) {
            if (is_array($property_val)) {
                if (in_array($property_key, self::SHADOW_ARRAY_KEYS, true)) {
<<<<<<< HEAD
                    /** @var null|array<mixed> */
                    $temp = &$this->shadowProperties[$property_key];
                    $reference = &$temp;
                    $this->setShadowPropertiesMapValues(
                        $property_val,
                        $reference
                    );
=======
                    $reference = &$this->shadowProperties[$property_key];
                    $this->setShadowPropertiesMapValues($property_val, $reference);
>>>>>>> main
                }
            } else {
                if ($base_reference === null) {
                    $this->shadowProperties[$property_key] = $property_val;
                } else {
                    $reference[$property_key] = $property_val;
                }
            }
        }

        return $this;
    }

    /**
     * Set Shadow Blur.
     *
<<<<<<< HEAD
     * @return $this
     */
    protected function setShadowBlur(?float $blur)
=======
     * @param ?float $blur
     *
     * @return $this
     */
    protected function setShadowBlur($blur)
>>>>>>> main
    {
        if ($blur !== null) {
            $this->shadowProperties['blur'] = $blur;
        }

        return $this;
    }

    /**
     * Set Shadow Angle.
     *
<<<<<<< HEAD
     * @return $this
     */
    protected function setShadowAngle(null|float|int|string $angle)
=======
     * @param null|float|int|string $angle
     *
     * @return $this
     */
    protected function setShadowAngle($angle)
>>>>>>> main
    {
        if (is_numeric($angle)) {
            $this->shadowProperties['direction'] = $angle;
        }

        return $this;
    }

    /**
     * Set Shadow Distance.
     *
<<<<<<< HEAD
     * @return $this
     */
    protected function setShadowDistance(?float $distance)
=======
     * @param ?float $distance
     *
     * @return $this
     */
    protected function setShadowDistance($distance)
>>>>>>> main
    {
        if ($distance !== null) {
            $this->shadowProperties['distance'] = $distance;
        }

        return $this;
    }

    public function getShadowColorObject(): ChartColor
    {
        return $this->shadowColor;
    }

    /**
     * Get Shadow Property.
     *
     * @param string|string[] $elements
     *
<<<<<<< HEAD
     * @return null|mixed[]|string
     */
    public function getShadowProperty($elements): array|string|null
=======
     * @return array|string
     */
    public function getShadowProperty($elements)
>>>>>>> main
    {
        if ($elements === 'color') {
            return [
                'value' => $this->shadowColor->getValue(),
                'type' => $this->shadowColor->getType(),
                'alpha' => $this->shadowColor->getAlpha(),
            ];
        }
<<<<<<< HEAD
        $retVal = $this->getArrayElementsValue($this->shadowProperties, $elements);
        if (is_scalar($retVal)) {
            $retVal = (string) $retVal;
        } elseif ($retVal !== null && !is_array($retVal)) {
            // @codeCoverageIgnoreStart
            throw new Exception('Unexpected value for shadowProperty');
            // @codeCoverageIgnoreEnd
        }

        return $retVal;
    }

    /** @return mixed[] */
=======

        return $this->getArrayElementsValue($this->shadowProperties, $elements);
    }

>>>>>>> main
    public function getShadowArray(): array
    {
        $array = $this->shadowProperties;
        if ($this->getShadowColorObject()->isUsable()) {
            $array['color'] = $this->getShadowProperty('color');
        }

        return $array;
    }

<<<<<<< HEAD
    protected ChartColor $lineColor;

    /** @var array{width: null|float|int|string, compound: ?string, dash: ?string, cap: ?string, join: ?string, arrow: array{head: array{type: ?string, size: null|int|string, w: ?string, len: ?string}, end: array{type: ?string, size: null|int|string, w: ?string, len: ?string}}} */
    protected array $lineStyleProperties = [
=======
    /** @var ChartColor */
    protected $lineColor;

    /** @var array */
    protected $lineStyleProperties = [
>>>>>>> main
        'width' => null, //'9525',
        'compound' => '', //self::LINE_STYLE_COMPOUND_SIMPLE,
        'dash' => '', //self::LINE_STYLE_DASH_SOLID,
        'cap' => '', //self::LINE_STYLE_CAP_FLAT,
        'join' => '', //self::LINE_STYLE_JOIN_BEVEL,
        'arrow' => [
            'head' => [
                'type' => '', //self::LINE_STYLE_ARROW_TYPE_NOARROW,
                'size' => '', //self::LINE_STYLE_ARROW_SIZE_5,
                'w' => '',
                'len' => '',
            ],
            'end' => [
                'type' => '', //self::LINE_STYLE_ARROW_TYPE_NOARROW,
                'size' => '', //self::LINE_STYLE_ARROW_SIZE_8,
                'w' => '',
                'len' => '',
            ],
        ],
    ];

    public function copyLineStyles(self $otherProperties): void
    {
        $this->lineStyleProperties = $otherProperties->lineStyleProperties;
        $this->lineColor = $otherProperties->lineColor;
        $this->glowSize = $otherProperties->glowSize;
        $this->glowColor = $otherProperties->glowColor;
        $this->softEdges = $otherProperties->softEdges;
        $this->shadowProperties = $otherProperties->shadowProperties;
    }

    public function getLineColor(): ChartColor
    {
        return $this->lineColor;
    }

    /**
     * Set Line Color Properties.
<<<<<<< HEAD
     */
    public function setLineColorProperties(?string $value, ?int $alpha = null, ?string $colorType = null): void
=======
     *
     * @param string $value
     * @param ?int $alpha
     * @param ?string $colorType
     */
    public function setLineColorProperties($value, $alpha = null, $colorType = null): void
>>>>>>> main
    {
        $this->activateObject();
        $this->lineColor->setColorPropertiesArray(
            $this->setColorProperties(
                $value,
                $alpha,
                $colorType
            )
        );
    }

    /**
     * Get Line Color Property.
<<<<<<< HEAD
     */
    public function getLineColorProperty(string $propertyName): null|int|string
=======
     *
     * @param string $propertyName
     *
     * @return null|int|string
     */
    public function getLineColorProperty($propertyName)
>>>>>>> main
    {
        return $this->lineColor->getColorProperty($propertyName);
    }

    /**
     * Set Line Style Properties.
<<<<<<< HEAD
     */
    public function setLineStyleProperties(
        null|float|int|string $lineWidth = null,
        ?string $compoundType = '',
        ?string $dashType = '',
        ?string $capType = '',
        ?string $joinType = '',
        ?string $headArrowType = '',
        int $headArrowSize = 0,
        ?string $endArrowType = '',
        int $endArrowSize = 0,
        ?string $headArrowWidth = '',
        ?string $headArrowLength = '',
        ?string $endArrowWidth = '',
        ?string $endArrowLength = ''
    ): void {
=======
     *
     * @param null|float|int|string $lineWidth
     * @param string $compoundType
     * @param string $dashType
     * @param string $capType
     * @param string $joinType
     * @param string $headArrowType
     * @param string $headArrowSize
     * @param string $endArrowType
     * @param string $endArrowSize
     * @param string $headArrowWidth
     * @param string $headArrowLength
     * @param string $endArrowWidth
     * @param string $endArrowLength
     */
    public function setLineStyleProperties($lineWidth = null, $compoundType = '', $dashType = '', $capType = '', $joinType = '', $headArrowType = '', $headArrowSize = '', $endArrowType = '', $endArrowSize = '', $headArrowWidth = '', $headArrowLength = '', $endArrowWidth = '', $endArrowLength = ''): void
    {
>>>>>>> main
        $this->activateObject();
        if (is_numeric($lineWidth)) {
            $this->lineStyleProperties['width'] = $lineWidth;
        }
        if ($compoundType !== '') {
            $this->lineStyleProperties['compound'] = $compoundType;
        }
        if ($dashType !== '') {
            $this->lineStyleProperties['dash'] = $dashType;
        }
        if ($capType !== '') {
            $this->lineStyleProperties['cap'] = $capType;
        }
        if ($joinType !== '') {
            $this->lineStyleProperties['join'] = $joinType;
        }
        if ($headArrowType !== '') {
            $this->lineStyleProperties['arrow']['head']['type'] = $headArrowType;
        }
<<<<<<< HEAD
        if (isset(self::ARROW_SIZES[$headArrowSize])) {
=======
        if (array_key_exists($headArrowSize, self::ARROW_SIZES)) {
>>>>>>> main
            $this->lineStyleProperties['arrow']['head']['size'] = $headArrowSize;
            $this->lineStyleProperties['arrow']['head']['w'] = self::ARROW_SIZES[$headArrowSize]['w'];
            $this->lineStyleProperties['arrow']['head']['len'] = self::ARROW_SIZES[$headArrowSize]['len'];
        }
        if ($endArrowType !== '') {
            $this->lineStyleProperties['arrow']['end']['type'] = $endArrowType;
        }
<<<<<<< HEAD
        if (isset(self::ARROW_SIZES[$endArrowSize])) {
=======
        if (array_key_exists($endArrowSize, self::ARROW_SIZES)) {
>>>>>>> main
            $this->lineStyleProperties['arrow']['end']['size'] = $endArrowSize;
            $this->lineStyleProperties['arrow']['end']['w'] = self::ARROW_SIZES[$endArrowSize]['w'];
            $this->lineStyleProperties['arrow']['end']['len'] = self::ARROW_SIZES[$endArrowSize]['len'];
        }
        if ($headArrowWidth !== '') {
            $this->lineStyleProperties['arrow']['head']['w'] = $headArrowWidth;
        }
        if ($headArrowLength !== '') {
            $this->lineStyleProperties['arrow']['head']['len'] = $headArrowLength;
        }
        if ($endArrowWidth !== '') {
            $this->lineStyleProperties['arrow']['end']['w'] = $endArrowWidth;
        }
        if ($endArrowLength !== '') {
            $this->lineStyleProperties['arrow']['end']['len'] = $endArrowLength;
        }
    }

<<<<<<< HEAD
    /** @return mixed[] */
=======
>>>>>>> main
    public function getLineStyleArray(): array
    {
        return $this->lineStyleProperties;
    }

<<<<<<< HEAD
    /** @param mixed[] $lineStyleProperties */
    public function setLineStyleArray(array $lineStyleProperties = []): self
    {
        /** @var array{width?: ?string, compound?: string, dash?: string, cap?: string, join?: string, arrow?: array{head?: array{type?: string, size?: int, w?: string, len?: string}, end?: array{type?: string, size?: int, w?: string, len?: string}}} $lineStyleProperties */
=======
    public function setLineStyleArray(array $lineStyleProperties = []): self
    {
>>>>>>> main
        $this->activateObject();
        $this->lineStyleProperties['width'] = $lineStyleProperties['width'] ?? null;
        $this->lineStyleProperties['compound'] = $lineStyleProperties['compound'] ?? '';
        $this->lineStyleProperties['dash'] = $lineStyleProperties['dash'] ?? '';
        $this->lineStyleProperties['cap'] = $lineStyleProperties['cap'] ?? '';
        $this->lineStyleProperties['join'] = $lineStyleProperties['join'] ?? '';
        $this->lineStyleProperties['arrow']['head']['type'] = $lineStyleProperties['arrow']['head']['type'] ?? '';
        $this->lineStyleProperties['arrow']['head']['size'] = $lineStyleProperties['arrow']['head']['size'] ?? '';
        $this->lineStyleProperties['arrow']['head']['w'] = $lineStyleProperties['arrow']['head']['w'] ?? '';
        $this->lineStyleProperties['arrow']['head']['len'] = $lineStyleProperties['arrow']['head']['len'] ?? '';
        $this->lineStyleProperties['arrow']['end']['type'] = $lineStyleProperties['arrow']['end']['type'] ?? '';
        $this->lineStyleProperties['arrow']['end']['size'] = $lineStyleProperties['arrow']['end']['size'] ?? '';
        $this->lineStyleProperties['arrow']['end']['w'] = $lineStyleProperties['arrow']['end']['w'] ?? '';
        $this->lineStyleProperties['arrow']['end']['len'] = $lineStyleProperties['arrow']['end']['len'] ?? '';

        return $this;
    }

<<<<<<< HEAD
    public function setLineStyleProperty(string $propertyName, mixed $value): self
    {
        $this->activateObject();
        $this->lineStyleProperties[$propertyName] = $value; //* @phpstan-ignore-line
=======
    /**
     * @param mixed $value
     */
    public function setLineStyleProperty(string $propertyName, $value): self
    {
        $this->activateObject();
        $this->lineStyleProperties[$propertyName] = $value;
>>>>>>> main

        return $this;
    }

    /**
     * Get Line Style Property.
     *
<<<<<<< HEAD
     * @param array<mixed>|string $elements
     */
    public function getLineStyleProperty(array|string $elements): ?string
    {
        $retVal = $this->getArrayElementsValue($this->lineStyleProperties, $elements);
        if (is_scalar($retVal)) {
            $retVal = (string) $retVal;
        } elseif ($retVal !== null) {
            // @codeCoverageIgnoreStart
            throw new Exception('Unexpected value for lineStyleProperty');
            // @codeCoverageIgnoreEnd
        }

        return $retVal;
=======
     * @param array|string $elements
     *
     * @return string
     */
    public function getLineStyleProperty($elements)
    {
        return $this->getArrayElementsValue($this->lineStyleProperties, $elements);
>>>>>>> main
    }

    protected const ARROW_SIZES = [
        1 => ['w' => 'sm', 'len' => 'sm'],
        2 => ['w' => 'sm', 'len' => 'med'],
        3 => ['w' => 'sm', 'len' => 'lg'],
        4 => ['w' => 'med', 'len' => 'sm'],
        5 => ['w' => 'med', 'len' => 'med'],
        6 => ['w' => 'med', 'len' => 'lg'],
        7 => ['w' => 'lg', 'len' => 'sm'],
        8 => ['w' => 'lg', 'len' => 'med'],
        9 => ['w' => 'lg', 'len' => 'lg'],
    ];

    /**
     * Get Line Style Arrow Size.
<<<<<<< HEAD
     */
    protected function getLineStyleArrowSize(int $arraySelector, string $arrayKaySelector): string
=======
     *
     * @param int $arraySelector
     * @param string $arrayKaySelector
     *
     * @return string
     */
    protected function getLineStyleArrowSize($arraySelector, $arrayKaySelector)
>>>>>>> main
    {
        return self::ARROW_SIZES[$arraySelector][$arrayKaySelector] ?? '';
    }

    /**
     * Get Line Style Arrow Parameters.
<<<<<<< HEAD
     */
    public function getLineStyleArrowParameters(string $arrowSelector, string $propertySelector): string
    {
        return $this->getLineStyleArrowSize((int) $this->lineStyleProperties['arrow'][$arrowSelector]['size'], $propertySelector);
=======
     *
     * @param string $arrowSelector
     * @param string $propertySelector
     *
     * @return string
     */
    public function getLineStyleArrowParameters($arrowSelector, $propertySelector)
    {
        return $this->getLineStyleArrowSize($this->lineStyleProperties['arrow'][$arrowSelector]['size'], $propertySelector);
>>>>>>> main
    }

    /**
     * Get Line Style Arrow Width.
<<<<<<< HEAD
     */
    public function getLineStyleArrowWidth(string $arrow): ?string
=======
     *
     * @param string $arrow
     *
     * @return string
     */
    public function getLineStyleArrowWidth($arrow)
>>>>>>> main
    {
        return $this->getLineStyleProperty(['arrow', $arrow, 'w']);
    }

    /**
     * Get Line Style Arrow Excel Length.
<<<<<<< HEAD
     */
    public function getLineStyleArrowLength(string $arrow): ?string
    {
        return $this->getLineStyleProperty(['arrow', $arrow, 'len']);
    }

    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     */
    public function __clone()
    {
        $this->lineColor = clone $this->lineColor;
        $this->glowColor = clone $this->glowColor;
        $this->shadowColor = clone $this->shadowColor;
    }
=======
     *
     * @param string $arrow
     *
     * @return string
     */
    public function getLineStyleArrowLength($arrow)
    {
        return $this->getLineStyleProperty(['arrow', $arrow, 'len']);
    }
>>>>>>> main
}
