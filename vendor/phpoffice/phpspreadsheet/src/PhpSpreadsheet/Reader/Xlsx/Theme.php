<?php

namespace PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Theme
{
    /**
     * Theme Name.
<<<<<<< HEAD
     */
    private string $themeName;

    /**
     * Colour Scheme Name.
     */
    private string $colourSchemeName;
=======
     *
     * @var string
     */
    private $themeName;

    /**
     * Colour Scheme Name.
     *
     * @var string
     */
    private $colourSchemeName;
>>>>>>> main

    /**
     * Colour Map.
     *
     * @var string[]
     */
<<<<<<< HEAD
    private array $colourMap;
=======
    private $colourMap;
>>>>>>> main

    /**
     * Create a new Theme.
     *
<<<<<<< HEAD
     * @param string[] $colourMap
     */
    public function __construct(string $themeName, string $colourSchemeName, array $colourMap)
=======
     * @param string $themeName
     * @param string $colourSchemeName
     * @param string[] $colourMap
     */
    public function __construct($themeName, $colourSchemeName, $colourMap)
>>>>>>> main
    {
        // Initialise values
        $this->themeName = $themeName;
        $this->colourSchemeName = $colourSchemeName;
        $this->colourMap = $colourMap;
    }

    /**
     * Not called by Reader, never accessible any other time.
     *
<<<<<<< HEAD
     * @codeCoverageIgnore
     */
    public function getThemeName(): string
=======
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getThemeName()
>>>>>>> main
    {
        return $this->themeName;
    }

    /**
     * Not called by Reader, never accessible any other time.
     *
<<<<<<< HEAD
     * @codeCoverageIgnore
     */
    public function getColourSchemeName(): string
=======
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getColourSchemeName()
>>>>>>> main
    {
        return $this->colourSchemeName;
    }

    /**
     * Get colour Map Value by Position.
<<<<<<< HEAD
     */
    public function getColourByIndex(int $index): ?string
=======
     *
     * @param int $index
     *
     * @return null|string
     */
    public function getColourByIndex($index)
>>>>>>> main
    {
        return $this->colourMap[$index] ?? null;
    }
}
