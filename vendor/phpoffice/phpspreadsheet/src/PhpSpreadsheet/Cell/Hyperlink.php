<?php

namespace PhpOffice\PhpSpreadsheet\Cell;

class Hyperlink
{
    /**
     * URL to link the cell to.
<<<<<<< HEAD
     */
    private string $url;

    /**
     * Tooltip to display on the hyperlink.
     */
    private string $tooltip;

    private string $display = '';
=======
     *
     * @var string
     */
    private $url;

    /**
     * Tooltip to display on the hyperlink.
     *
     * @var string
     */
    private $tooltip;
>>>>>>> main

    /**
     * Create a new Hyperlink.
     *
     * @param string $url Url to link the cell to
     * @param string $tooltip Tooltip to display on the hyperlink
     */
<<<<<<< HEAD
    public function __construct(string $url = '', string $tooltip = '')
=======
    public function __construct($url = '', $tooltip = '')
>>>>>>> main
    {
        // Initialise member variables
        $this->url = $url;
        $this->tooltip = $tooltip;
    }

    /**
     * Get URL.
<<<<<<< HEAD
     */
    public function getUrl(): string
=======
     *
     * @return string
     */
    public function getUrl()
>>>>>>> main
    {
        return $this->url;
    }

    /**
     * Set URL.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setUrl(string $url): static
=======
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
>>>>>>> main
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get tooltip.
<<<<<<< HEAD
     */
    public function getTooltip(): string
=======
     *
     * @return string
     */
    public function getTooltip()
>>>>>>> main
    {
        return $this->tooltip;
    }

    /**
     * Set tooltip.
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setTooltip(string $tooltip): static
=======
     * @param string $tooltip
     *
     * @return $this
     */
    public function setTooltip($tooltip)
>>>>>>> main
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    /**
<<<<<<< HEAD
     * Is this hyperlink internal? (to another worksheet or a cell in this worksheet).
     */
    public function isInternal(): bool
    {
        return str_starts_with($this->url, 'sheet://') || str_starts_with($this->url, '#');
    }

    public function getTypeHyperlink(): string
    {
        return $this->isInternal() ? '' : 'External';
    }

    public function getDisplay(): string
    {
        return $this->display;
    }

    /**
     * This can be displayed in cell rather than actual cell contents.
     * It seems to be ignored by Excel.
     * It may be used by Google Sheets.
     */
    public function setDisplay(string $display): self
    {
        $this->display = $display;

        return $this;
=======
     * Is this hyperlink internal? (to another worksheet).
     *
     * @return bool
     */
    public function isInternal()
    {
        return strpos($this->url, 'sheet://') !== false;
    }

    /**
     * @return string
     */
    public function getTypeHyperlink()
    {
        return $this->isInternal() ? '' : 'External';
>>>>>>> main
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
            $this->url
            . ','
            . $this->tooltip
            . ','
            . $this->display
            . ','
            . __CLASS__
=======
    public function getHashCode()
    {
        return md5(
            $this->url .
            $this->tooltip .
            __CLASS__
>>>>>>> main
        );
    }
}
