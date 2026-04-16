<?php

namespace PhpOffice\PhpSpreadsheet\Worksheet;

use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use ZipArchive;

class Drawing extends BaseDrawing
{
    const IMAGE_TYPES_CONVERTION_MAP = [
        IMAGETYPE_GIF => IMAGETYPE_PNG,
        IMAGETYPE_JPEG => IMAGETYPE_JPEG,
        IMAGETYPE_PNG => IMAGETYPE_PNG,
        IMAGETYPE_BMP => IMAGETYPE_PNG,
    ];

    /**
     * Path.
<<<<<<< HEAD
     */
    private string $path;

    /**
     * Whether or not we are dealing with a URL.
     */
    private bool $isUrl;
=======
     *
     * @var string
     */
    private $path;

    /**
     * Whether or not we are dealing with a URL.
     *
     * @var bool
     */
    private $isUrl;
>>>>>>> main

    /**
     * Create a new Drawing.
     */
    public function __construct()
    {
        // Initialise values
        $this->path = '';
        $this->isUrl = false;

        // Initialize parent
        parent::__construct();
    }

    /**
     * Get Filename.
<<<<<<< HEAD
     */
    public function getFilename(): string
=======
     *
     * @return string
     */
    public function getFilename()
>>>>>>> main
    {
        return basename($this->path);
    }

    /**
     * Get indexed filename (using image index).
     */
    public function getIndexedFilename(): string
    {
        return md5($this->path) . '.' . $this->getExtension();
    }

    /**
     * Get Extension.
<<<<<<< HEAD
     */
    public function getExtension(): string
=======
     *
     * @return string
     */
    public function getExtension()
>>>>>>> main
    {
        $exploded = explode('.', basename($this->path));

        return $exploded[count($exploded) - 1];
    }

    /**
     * Get full filepath to store drawing in zip archive.
<<<<<<< HEAD
     */
    public function getMediaFilename(): string
=======
     *
     * @return string
     */
    public function getMediaFilename()
>>>>>>> main
    {
        if (!array_key_exists($this->type, self::IMAGE_TYPES_CONVERTION_MAP)) {
            throw new PhpSpreadsheetException('Unsupported image type in comment background. Supported types: PNG, JPEG, BMP, GIF.');
        }

        return sprintf('image%d%s', $this->getImageIndex(), $this->getImageFileExtensionForSave());
    }

    /**
     * Get Path.
<<<<<<< HEAD
     */
    public function getPath(): string
=======
     *
     * @return string
     */
    public function getPath()
>>>>>>> main
    {
        return $this->path;
    }

    /**
     * Set Path.
     *
     * @param string $path File path
     * @param bool $verifyFile Verify file
<<<<<<< HEAD
     * @param ?ZipArchive $zip Zip archive instance
=======
     * @param ZipArchive $zip Zip archive instance
     * @param bool $allowExternal
>>>>>>> main
     * @param null|callable(string):bool $isWhitelisted
     *
     * @return $this
     */
<<<<<<< HEAD
    public function setPath(string $path, bool $verifyFile = true, ?ZipArchive $zip = null, bool $allowExternal = true, ?callable $isWhitelisted = null): static
=======
    public function setPath($path, $verifyFile = true, $zip = null, $allowExternal = true, ?callable $isWhitelisted = null)
>>>>>>> main
    {
        $this->isUrl = false;
        if (preg_match('~^data:image/[a-z]+;base64,~', $path) === 1) {
            $this->path = $path;

            return $this;
        }

        $this->path = '';
        if ($zip instanceof ZipArchive) {
            $zipPath = explode('#', $path)[1];
            $locate = @$zip->locateName($zipPath);
            if ($locate !== false) {
                if ($this->isImage($path)) {
                    $this->path = $path;
                    $this->setSizesAndType($path);
                }
            }
        // Check if a URL has been passed. https://stackoverflow.com/a/2058596/1252979
        } elseif (filter_var($path, FILTER_VALIDATE_URL) || (preg_match('/^([\w\s\x00-\x1f]+):/u', $path) && !preg_match('/^([\w]+):/u', $path))) {
            if (!preg_match('/^(http|https|file|ftp|s3):/', $path)) {
                throw new PhpSpreadsheetException('Invalid protocol for linked drawing');
            }
            if (!$allowExternal) {
                return $this;
            }
            if ($isWhitelisted !== null && !$isWhitelisted($path)) {
                return $this;
            }
            // Implicit that it is a URL, rather store info than running check above on value in other places.
            $this->isUrl = true;
            $ctx = null;
            // https://github.com/php/php-src/issues/16023
            // https://github.com/php/php-src/issues/17121
<<<<<<< HEAD
            if (str_starts_with($path, 'https:') || str_starts_with($path, 'http:')) {
=======
            if (preg_match('/^https?:/', $path) === 1) {
>>>>>>> main
                $ctxArray = [
                    'http' => [
                        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                        'header' => [
                            //'Connection: keep-alive', // unacceptable performance
                            'Accept: image/*;q=0.9,*/*;q=0.8',
                        ],
                    ],
                ];
<<<<<<< HEAD
                if (str_starts_with($path, 'https:')) {
=======
                if (preg_match('/^https:/', $path) === 1) {
>>>>>>> main
                    $ctxArray['ssl'] = ['crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT];
                }
                $ctx = stream_context_create($ctxArray);
            }
            $imageContents = @file_get_contents($path, false, $ctx);
            if ($imageContents !== false) {
                $filePath = tempnam(sys_get_temp_dir(), 'Drawing');
                if ($filePath) {
                    $put = @file_put_contents($filePath, $imageContents);
                    if ($put !== false) {
                        if ($this->isImage($filePath)) {
                            $this->path = $path;
                            $this->setSizesAndType($filePath);
                        }
                        unlink($filePath);
                    }
                }
            }
        } else {
            $exists = @file_exists($path);
            if ($exists !== false && $this->isImage($path)) {
                $this->path = $path;
                $this->setSizesAndType($path);
            }
        }
        if ($this->path === '' && $verifyFile) {
            throw new PhpSpreadsheetException("File $path not found!");
        }

        if ($this->worksheet !== null) {
            if ($this->path !== '') {
                $this->worksheet->getCell($this->coordinates);
            }
        }

        return $this;
    }

    private function isImage(string $path): bool
    {
        $mime = (string) @mime_content_type($path);
        $retVal = false;
<<<<<<< HEAD
        if (str_starts_with($mime, 'image/')) {
=======
        if (strpos($mime, 'image/') === 0) {
>>>>>>> main
            $retVal = true;
        } elseif ($mime === 'application/octet-stream') {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $retVal = in_array($extension, ['bin', 'emf'], true);
        }

        return $retVal;
    }

    /**
     * Get isURL.
     */
    public function getIsURL(): bool
    {
        return $this->isUrl;
    }

    /**
<<<<<<< HEAD
=======
     * Set isURL.
     *
     * @return $this
     *
     * @deprecated 3.7.0 not needed, property is set by setPath
     */
    public function setIsURL(bool $isUrl): self
    {
        $this->isUrl = $isUrl;

        return $this;
    }

    /**
>>>>>>> main
     * Get hash code.
     *
     * @return string Hash code
     */
<<<<<<< HEAD
    public function getHashCode(): string
    {
        return md5(
            $this->path
            . parent::getHashCode()
            . __CLASS__
=======
    public function getHashCode()
    {
        return md5(
            $this->path .
            parent::getHashCode() .
            __CLASS__
>>>>>>> main
        );
    }

    /**
     * Get Image Type for Save.
     */
    public function getImageTypeForSave(): int
    {
        if (!array_key_exists($this->type, self::IMAGE_TYPES_CONVERTION_MAP)) {
            throw new PhpSpreadsheetException('Unsupported image type in comment background. Supported types: PNG, JPEG, BMP, GIF.');
        }

        return self::IMAGE_TYPES_CONVERTION_MAP[$this->type];
    }

    /**
<<<<<<< HEAD
     * Get Image file extension for Save.
=======
     * Get Image file extention for Save.
>>>>>>> main
     */
    public function getImageFileExtensionForSave(bool $includeDot = true): string
    {
        if (!array_key_exists($this->type, self::IMAGE_TYPES_CONVERTION_MAP)) {
            throw new PhpSpreadsheetException('Unsupported image type in comment background. Supported types: PNG, JPEG, BMP, GIF.');
        }

        $result = image_type_to_extension(self::IMAGE_TYPES_CONVERTION_MAP[$this->type], $includeDot);

        return "$result";
    }

    /**
     * Get Image mime type.
     */
    public function getImageMimeType(): string
    {
        if (!array_key_exists($this->type, self::IMAGE_TYPES_CONVERTION_MAP)) {
            throw new PhpSpreadsheetException('Unsupported image type in comment background. Supported types: PNG, JPEG, BMP, GIF.');
        }

        return image_type_to_mime_type(self::IMAGE_TYPES_CONVERTION_MAP[$this->type]);
    }
}
