<?php

namespace PhpOffice\PhpSpreadsheet\RichText;

<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Style\Font;

=======
>>>>>>> main
interface ITextElement
{
    /**
     * Get text.
<<<<<<< HEAD
     */
    public function getText(): string;
=======
     *
     * @return string Text
     */
    public function getText();
>>>>>>> main

    /**
     * Set text.
     *
     * @param string $text Text
     *
<<<<<<< HEAD
     * @return $this
     */
    public function setText(string $text): self;

    /**
     * Get font.
     */
    public function getFont(): ?Font;
=======
     * @return ITextElement
     */
    public function setText($text);

    /**
     * Get font.
     *
     * @return null|\PhpOffice\PhpSpreadsheet\Style\Font
     */
    public function getFont();
>>>>>>> main

    /**
     * Get hash code.
     *
     * @return string Hash code
     */
<<<<<<< HEAD
    public function getHashCode(): string;
=======
    public function getHashCode();
>>>>>>> main
}
