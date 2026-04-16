<?php

namespace PhpOffice\PhpSpreadsheet\Worksheet;

use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;

class SheetView
{
    // Sheet View types
    const SHEETVIEW_NORMAL = 'normal';
    const SHEETVIEW_PAGE_LAYOUT = 'pageLayout';
    const SHEETVIEW_PAGE_BREAK_PREVIEW = 'pageBreakPreview';

    private const SHEET_VIEW_TYPES = [
        self::SHEETVIEW_NORMAL,
        self::SHEETVIEW_PAGE_LAYOUT,
        self::SHEETVIEW_PAGE_BREAK_PREVIEW,
    ];

    /**
     * ZoomScale.
     *
     * Valid values range from 10 to 400.
<<<<<<< HEAD
     */
    private ?int $zoomScale = 100;
=======
     *
     * @var ?int
     */
    private $zoomScale = 100;
>>>>>>> main

    /**
     * ZoomScaleNormal.
     *
     * Valid values range from 10 to 400.
<<<<<<< HEAD
     */
    private ?int $zoomScaleNormal = 100;

    /**
     * ZoomScalePageLayoutView.
     *
     * Valid values range from 10 to 400.
     */
    private int $zoomScalePageLayoutView = 100;

    /**
     * ZoomScaleSheetLayoutView.
     *
     * Valid values range from 10 to 400.
     */
    private int $zoomScaleSheetLayoutView = 100;
=======
     *
     * @var ?int
     */
    private $zoomScaleNormal = 100;
>>>>>>> main

    /**
     * ShowZeros.
     *
     * If true, "null" values from a calculation will be shown as "0". This is the default Excel behaviour and can be changed
     * with the advanced worksheet option "Show a zero in cells that have zero value"
<<<<<<< HEAD
     */
    private bool $showZeros = true;
=======
     *
     * @var bool
     */
    private $showZeros = true;
>>>>>>> main

    /**
     * View.
     *
     * Valid values range from 10 to 400.
<<<<<<< HEAD
     */
    private string $sheetviewType = self::SHEETVIEW_NORMAL;
=======
     *
     * @var string
     */
    private $sheetviewType = self::SHEETVIEW_NORMAL;
>>>>>>> main

    /**
     * Create a new SheetView.
     */
    public function __construct()
    {
    }

    /**
     * Get ZoomScale.
<<<<<<< HEAD
     */
    public function getZoomScale(): ?int
=======
     *
     * @return ?int
     */
    public function getZoomScale()
>>>>>>> main
    {
        return $this->zoomScale;
    }

    /**
     * Set ZoomScale.
     * Valid values range from 10 to 400.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setZoomScale(?int $zoomScale): static
=======
     * @param ?int $zoomScale
     *
     * @return $this
     */
    public function setZoomScale($zoomScale)
>>>>>>> main
    {
        // Microsoft Office Excel 2007 only allows setting a scale between 10 and 400 via the user interface,
        // but it is apparently still able to handle any scale >= 1
        if ($zoomScale === null || $zoomScale >= 1) {
            $this->zoomScale = $zoomScale;
        } else {
            throw new PhpSpreadsheetException('Scale must be greater than or equal to 1.');
        }

        return $this;
    }

    /**
     * Get ZoomScaleNormal.
<<<<<<< HEAD
     */
    public function getZoomScaleNormal(): ?int
=======
     *
     * @return ?int
     */
    public function getZoomScaleNormal()
>>>>>>> main
    {
        return $this->zoomScaleNormal;
    }

    /**
     * Set ZoomScale.
     * Valid values range from 10 to 400.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setZoomScaleNormal(?int $zoomScaleNormal): static
=======
     * @param ?int $zoomScaleNormal
     *
     * @return $this
     */
    public function setZoomScaleNormal($zoomScaleNormal)
>>>>>>> main
    {
        if ($zoomScaleNormal === null || $zoomScaleNormal >= 1) {
            $this->zoomScaleNormal = $zoomScaleNormal;
        } else {
            throw new PhpSpreadsheetException('Scale must be greater than or equal to 1.');
        }

        return $this;
    }

<<<<<<< HEAD
    public function getZoomScalePageLayoutView(): int
    {
        return $this->zoomScalePageLayoutView;
    }

    public function setZoomScalePageLayoutView(int $zoomScalePageLayoutView): static
    {
        if ($zoomScalePageLayoutView >= 1) {
            $this->zoomScalePageLayoutView = $zoomScalePageLayoutView;
        } else {
            throw new PhpSpreadsheetException('Scale must be greater than or equal to 1.');
        }

        return $this;
    }

    public function getZoomScaleSheetLayoutView(): int
    {
        return $this->zoomScaleSheetLayoutView;
    }

    public function setZoomScaleSheetLayoutView(int $zoomScaleSheetLayoutView): static
    {
        if ($zoomScaleSheetLayoutView >= 1) {
            $this->zoomScaleSheetLayoutView = $zoomScaleSheetLayoutView;
        } else {
            throw new PhpSpreadsheetException('Scale must be greater than or equal to 1.');
        }

        return $this;
    }

    /**
     * Set ShowZeroes setting.
     */
    public function setShowZeros(bool $showZeros): void
=======
    /**
     * Set ShowZeroes setting.
     *
     * @param bool $showZeros
     */
    public function setShowZeros($showZeros): void
>>>>>>> main
    {
        $this->showZeros = $showZeros;
    }

<<<<<<< HEAD
    public function getShowZeros(): bool
=======
    /**
     * @return bool
     */
    public function getShowZeros()
>>>>>>> main
    {
        return $this->showZeros;
    }

    /**
     * Get View.
<<<<<<< HEAD
     */
    public function getView(): string
=======
     *
     * @return string
     */
    public function getView()
>>>>>>> main
    {
        return $this->sheetviewType;
    }

    /**
     * Set View.
     *
     * Valid values are
     *        'normal'            self::SHEETVIEW_NORMAL
     *        'pageLayout'        self::SHEETVIEW_PAGE_LAYOUT
     *        'pageBreakPreview'  self::SHEETVIEW_PAGE_BREAK_PREVIEW
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setView(?string $sheetViewType): static
=======
     * @param ?string $sheetViewType
     *
     * @return $this
     */
    public function setView($sheetViewType)
>>>>>>> main
    {
        // MS Excel 2007 allows setting the view to 'normal', 'pageLayout' or 'pageBreakPreview' via the user interface
        if ($sheetViewType === null) {
            $sheetViewType = self::SHEETVIEW_NORMAL;
        }
        if (in_array($sheetViewType, self::SHEET_VIEW_TYPES)) {
            $this->sheetviewType = $sheetViewType;
        } else {
            throw new PhpSpreadsheetException('Invalid sheetview layout type.');
        }

        return $this;
    }
}
