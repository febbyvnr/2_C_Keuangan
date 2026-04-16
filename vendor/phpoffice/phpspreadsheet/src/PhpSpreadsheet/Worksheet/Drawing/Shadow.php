<?php

namespace PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use PhpOffice\PhpSpreadsheet\IComparable;
use PhpOffice\PhpSpreadsheet\Style\Color;

class Shadow implements IComparable
{
    // Shadow alignment
    const SHADOW_BOTTOM = 'b';
    const SHADOW_BOTTOM_LEFT = 'bl';
    const SHADOW_BOTTOM_RIGHT = 'br';
    const SHADOW_CENTER = 'ctr';
    const SHADOW_LEFT = 'l';
    const SHADOW_TOP = 't';
    const SHADOW_TOP_LEFT = 'tl';
    const SHADOW_TOP_RIGHT = 'tr';

    /**
     * Visible.
<<<<<<< HEAD
     */
    private bool $visible;
=======
     *
     * @var bool
     */
    private $visible;
>>>>>>> main

    /**
     * Blur radius.
     *
     * Defaults to 6
<<<<<<< HEAD
     */
    private int $blurRadius;
=======
     *
     * @var int
     */
    private $blurRadius;
>>>>>>> main

    /**
     * Shadow distance.
     *
     * Defaults to 2
<<<<<<< HEAD
     */
    private int $distance;

    /**
     * Shadow direction (in degrees).
     */
    private int $direction;

    /**
     * Shadow alignment.
     */
    private string $alignment;

    /**
     * Color.
     */
    private Color $color;

    /**
     * Alpha.
     */
    private int $alpha;
=======
     *
     * @var int
     */
    private $distance;

    /**
     * Shadow direction (in degrees).
     *
     * @var int
     */
    private $direction;

    /**
     * Shadow alignment.
     *
     * @var string
     */
    private $alignment;

    /**
     * Color.
     *
     * @var Color
     */
    private $color;

    /**
     * Alpha.
     *
     * @var int
     */
    private $alpha;
>>>>>>> main

    /**
     * Create a new Shadow.
     */
    public function __construct()
    {
        // Initialise values
        $this->visible = false;
        $this->blurRadius = 6;
        $this->distance = 2;
        $this->direction = 0;
        $this->alignment = self::SHADOW_BOTTOM_RIGHT;
        $this->color = new Color(Color::COLOR_BLACK);
        $this->alpha = 50;
    }

    /**
     * Get Visible.
<<<<<<< HEAD
     */
    public function getVisible(): bool
=======
     *
     * @return bool
     */
    public function getVisible()
>>>>>>> main
    {
        return $this->visible;
    }

    /**
     * Set Visible.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setVisible(bool $visible): static
=======
     * @param bool $visible
     *
     * @return $this
     */
    public function setVisible($visible)
>>>>>>> main
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get Blur radius.
<<<<<<< HEAD
     */
    public function getBlurRadius(): int
=======
     *
     * @return int
     */
    public function getBlurRadius()
>>>>>>> main
    {
        return $this->blurRadius;
    }

    /**
     * Set Blur radius.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setBlurRadius(int $blurRadius): static
=======
     * @param int $blurRadius
     *
     * @return $this
     */
    public function setBlurRadius($blurRadius)
>>>>>>> main
    {
        $this->blurRadius = $blurRadius;

        return $this;
    }

    /**
     * Get Shadow distance.
<<<<<<< HEAD
     */
    public function getDistance(): int
=======
     *
     * @return int
     */
    public function getDistance()
>>>>>>> main
    {
        return $this->distance;
    }

    /**
     * Set Shadow distance.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setDistance(int $distance): static
=======
     * @param int $distance
     *
     * @return $this
     */
    public function setDistance($distance)
>>>>>>> main
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get Shadow direction (in degrees).
<<<<<<< HEAD
     */
    public function getDirection(): int
=======
     *
     * @return int
     */
    public function getDirection()
>>>>>>> main
    {
        return $this->direction;
    }

    /**
     * Set Shadow direction (in degrees).
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setDirection(int $direction): static
=======
     * @param int $direction
     *
     * @return $this
     */
    public function setDirection($direction)
>>>>>>> main
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * Get Shadow alignment.
<<<<<<< HEAD
     */
    public function getAlignment(): string
=======
     *
     * @return string
     */
    public function getAlignment()
>>>>>>> main
    {
        return $this->alignment;
    }

    /**
     * Set Shadow alignment.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setAlignment(string $alignment): static
=======
     * @param string $alignment
     *
     * @return $this
     */
    public function setAlignment($alignment)
>>>>>>> main
    {
        $this->alignment = $alignment;

        return $this;
    }

    /**
     * Get Color.
<<<<<<< HEAD
     */
    public function getColor(): Color
=======
     *
     * @return Color
     */
    public function getColor()
>>>>>>> main
    {
        return $this->color;
    }

    /**
     * Set Color.
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setColor(Color $color): static
=======
    public function setColor(Color $color)
>>>>>>> main
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get Alpha.
<<<<<<< HEAD
     */
    public function getAlpha(): int
=======
     *
     * @return int
     */
    public function getAlpha()
>>>>>>> main
    {
        return $this->alpha;
    }

    /**
     * Set Alpha.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setAlpha(int $alpha): static
=======
     * @param int $alpha
     *
     * @return $this
     */
    public function setAlpha($alpha)
>>>>>>> main
    {
        $this->alpha = $alpha;

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
            ($this->visible ? 't' : 'f')
            . $this->blurRadius
            . $this->distance
            . $this->direction
            . $this->alignment
            . $this->color->getHashCode()
            . $this->alpha
            . __CLASS__
=======
    public function getHashCode()
    {
        return md5(
            ($this->visible ? 't' : 'f') .
            $this->blurRadius .
            $this->distance .
            $this->direction .
            $this->alignment .
            $this->color->getHashCode() .
            $this->alpha .
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
}
