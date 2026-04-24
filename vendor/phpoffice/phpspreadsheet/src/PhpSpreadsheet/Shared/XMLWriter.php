<?php

namespace PhpOffice\PhpSpreadsheet\Shared;

use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;

class XMLWriter extends \XMLWriter
{
<<<<<<< HEAD
    public static bool $debugEnabled = false;
=======
    /** @var bool */
    public static $debugEnabled = false;
>>>>>>> main

    /** Temporary storage method */
    const STORAGE_MEMORY = 1;
    const STORAGE_DISK = 2;

    /**
     * Temporary filename.
<<<<<<< HEAD
     */
    private string $tempFileName = '';
=======
     *
     * @var string
     */
    private $tempFileName = '';
>>>>>>> main

    /**
     * Create a new XMLWriter instance.
     *
     * @param int $temporaryStorage Temporary storage location
<<<<<<< HEAD
     * @param ?string $temporaryStorageFolder Temporary storage folder
     */
    public function __construct(int $temporaryStorage = self::STORAGE_MEMORY, ?string $temporaryStorageFolder = null)
=======
     * @param string $temporaryStorageFolder Temporary storage folder
     */
    public function __construct($temporaryStorage = self::STORAGE_MEMORY, $temporaryStorageFolder = null)
>>>>>>> main
    {
        // Open temporary storage
        if ($temporaryStorage == self::STORAGE_MEMORY) {
            $this->openMemory();
        } else {
            // Create temporary filename
            if ($temporaryStorageFolder === null) {
                $temporaryStorageFolder = File::sysGetTempDir();
            }
            $this->tempFileName = (string) @tempnam($temporaryStorageFolder, 'xml');

            // Open storage
            if (empty($this->tempFileName) || $this->openUri($this->tempFileName) === false) {
                // Fallback to memory...
                $this->openMemory();
<<<<<<< HEAD
                if ($this->tempFileName != '') {
                    @unlink($this->tempFileName);
                }
                $this->tempFileName = '';
=======
>>>>>>> main
            }
        }

        // Set default values
        if (self::$debugEnabled) {
            $this->setIndent(true);
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        // Unlink temporary files
        // There is nothing reasonable to do if unlink fails.
        if ($this->tempFileName != '') {
<<<<<<< HEAD
=======
            /** @scrutinizer ignore-unhandled */
>>>>>>> main
            @unlink($this->tempFileName);
        }
    }

<<<<<<< HEAD
    /** @param mixed[] $data */
    public function __unserialize(array $data): void
=======
    public function __wakeup(): void
>>>>>>> main
    {
        $this->tempFileName = '';

        throw new SpreadsheetException('Unserialize not permitted');
    }

    /**
     * Get written data.
<<<<<<< HEAD
     */
    public function getData(): string
=======
     *
     * @return string
     */
    public function getData()
>>>>>>> main
    {
        if ($this->tempFileName == '') {
            return $this->outputMemory(true);
        }
        $this->flush();

        return file_get_contents($this->tempFileName) ?: '';
    }

    /**
     * Wrapper method for writeRaw.
     *
     * @param null|string|string[] $rawTextData
<<<<<<< HEAD
     */
    public function writeRawData($rawTextData): bool
=======
     *
     * @return bool
     */
    public function writeRawData($rawTextData)
>>>>>>> main
    {
        if (is_array($rawTextData)) {
            $rawTextData = implode("\n", $rawTextData);
        }

<<<<<<< HEAD
        return $this->text($rawTextData ?? '');
=======
        return $this->writeRaw(htmlspecialchars($rawTextData ?? ''));
>>>>>>> main
    }
}
