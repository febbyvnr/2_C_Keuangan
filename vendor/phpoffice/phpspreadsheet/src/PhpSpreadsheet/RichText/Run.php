<?php

namespace PhpOffice\PhpSpreadsheet\RichText;

<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;
=======
>>>>>>> main
use PhpOffice\PhpSpreadsheet\Style\Font;

class Run extends TextElement implements ITextElement
{
    /**
     * Font.
<<<<<<< HEAD
     */
    private ?Font $font;
=======
     *
     * @var ?Font
     */
    private $font;
>>>>>>> main

    /**
     * Create a new Run instance.
     *
     * @param string $text Text
     */
<<<<<<< HEAD
    public function __construct(string $text = '')
=======
    public function __construct($text = '')
>>>>>>> main
    {
        parent::__construct($text);
        // Initialise variables
        $this->font = new Font();
    }

    /**
     * Get font.
<<<<<<< HEAD
     */
    public function getFont(): ?Font
=======
     *
     * @return null|\PhpOffice\PhpSpreadsheet\Style\Font
     */
    public function getFont()
>>>>>>> main
    {
        return $this->font;
    }

<<<<<<< HEAD
    public function getFontOrThrow(): Font
    {
        if ($this->font === null) {
            throw new SpreadsheetException('unexpected null font');
        }

        return $this->font;
    }

    /**
     * Set font.
     *
     * @param ?Font $font Font
     *
     * @return $this
     */
    public function setFont(?Font $font = null): static
=======
    /**
     * Set font.
     *
     * @param Font $font Font
     *
     * @return $this
     */
    public function setFont(?Font $font = null)
>>>>>>> main
    {
        $this->font = $font;

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
            $this->getText()
            . (($this->font === null) ? '' : $this->font->getHashCode())
            . __CLASS__
=======
    public function getHashCode()
    {
        return md5(
            $this->getText() .
            (($this->font === null) ? '' : $this->font->getHashCode()) .
            __CLASS__
>>>>>>> main
        );
    }
}
