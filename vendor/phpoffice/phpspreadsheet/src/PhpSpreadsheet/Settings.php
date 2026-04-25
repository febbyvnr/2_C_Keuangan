<?php

namespace PhpOffice\PhpSpreadsheet;

use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Chart\Renderer\IRenderer;
use PhpOffice\PhpSpreadsheet\Collection\Memory;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;

class Settings
{
    /**
     * Class name of the chart renderer used for rendering charts
     * eg: PhpOffice\PhpSpreadsheet\Chart\Renderer\JpGraph.
     *
<<<<<<< HEAD
     * @var null|class-string<IRenderer>
     */
    private static ?string $chartRenderer = null;

    /**
     * The cache implementation to be used for cell collection.
     */
    private static ?CacheInterface $cache = null;

    private static mixed $httpClient = null;

    private static mixed $requestFactory = null;
=======
     * @var ?string
     */
    private static $chartRenderer;

    /**
     * Default options for libxml loader.
     *
     * @var ?int
     */
    private static $libXmlLoaderOptions;

    /**
     * The cache implementation to be used for cell collection.
     *
     * @var ?CacheInterface
     */
    private static $cache;

    /**
     * The HTTP client implementation to be used for network request.
     *
     * @var mixed
     */
    private static $httpClient;

    /**
     * @var mixed
     */
    private static $requestFactory;
>>>>>>> main

    /**
     * Set the locale code to use for formula translations and any special formatting.
     *
     * @param string $locale The locale code to use (e.g. "fr" or "pt_br" or "en_uk")
     *
     * @return bool Success or failure
     */
<<<<<<< HEAD
    public static function setLocale(string $locale): bool
=======
    public static function setLocale(string $locale)
>>>>>>> main
    {
        return Calculation::getInstance()->setLocale($locale);
    }

    public static function getLocale(): string
    {
        return Calculation::getInstance()->getLocale();
    }

    /**
     * Identify to PhpSpreadsheet the external library to use for rendering charts.
     *
<<<<<<< HEAD
     * @param class-string<IRenderer> $rendererClassName Class name of the chart renderer
=======
     * @param string $rendererClassName Class name of the chart renderer
>>>>>>> main
     *    eg: PhpOffice\PhpSpreadsheet\Chart\Renderer\JpGraph
     */
    public static function setChartRenderer(string $rendererClassName): void
    {
<<<<<<< HEAD
        // We want phpstan to validate caller, but still need this test
        if (!is_a($rendererClassName, IRenderer::class, true)) { //* @phpstan-ignore-line
=======
        if (!is_a($rendererClassName, IRenderer::class, true)) {
>>>>>>> main
            throw new Exception('Chart renderer must implement ' . IRenderer::class);
        }

        self::$chartRenderer = $rendererClassName;
    }

<<<<<<< HEAD
    public static function unsetChartRenderer(): void
    {
        self::$chartRenderer = null;
    }

    /**
     * Return the Chart Rendering Library that PhpSpreadsheet is currently configured to use.
     *
     * @return null|class-string<IRenderer> Class name of the chart renderer
=======
    /**
     * Return the Chart Rendering Library that PhpSpreadsheet is currently configured to use.
     *
     * @return null|string Class name of the chart renderer
>>>>>>> main
     *    eg: PhpOffice\PhpSpreadsheet\Chart\Renderer\JpGraph
     */
    public static function getChartRenderer(): ?string
    {
        return self::$chartRenderer;
    }

    public static function htmlEntityFlags(): int
    {
<<<<<<< HEAD
        return ENT_COMPAT;
=======
        return \ENT_COMPAT;
    }

    /**
     * Set default options for libxml loader.
     *
     * @param ?int $options Default options for libxml loader
     *
     * @deprecated 3.5.0 no longer needed
     */
    public static function setLibXmlLoaderOptions($options): int
    {
        if ($options === null) {
            $options = defined('LIBXML_DTDLOAD') ? (LIBXML_DTDLOAD | LIBXML_DTDATTR) : 0;
        }
        self::$libXmlLoaderOptions = $options;

        return $options;
    }

    /**
     * Get default options for libxml loader.
     * Defaults to LIBXML_DTDLOAD | LIBXML_DTDATTR when not set explicitly.
     *
     * @return int Default options for libxml loader
     *
     * @deprecated 3.5.0 no longer needed
     */
    public static function getLibXmlLoaderOptions(): int
    {
        return self::$libXmlLoaderOptions ?? (defined('LIBXML_DTDLOAD') ? (LIBXML_DTDLOAD | LIBXML_DTDATTR) : 0);
    }

    /**
     * Deprecated, has no effect.
     *
     * @param bool $state
     *
     * @deprecated will be removed without replacement as it is no longer necessary on PHP 7.3.0+
     *
     * @codeCoverageIgnore
     */
    public static function setLibXmlDisableEntityLoader(/** @scrutinizer ignore-unused */ $state): void
    {
        // noop
    }

    /**
     * Deprecated, has no effect.
     *
     * @return bool $state
     *
     * @deprecated will be removed without replacement as it is no longer necessary on PHP 7.3.0+
     *
     * @codeCoverageIgnore
     */
    public static function getLibXmlDisableEntityLoader(): bool
    {
        return true;
>>>>>>> main
    }

    /**
     * Sets the implementation of cache that should be used for cell collection.
     */
    public static function setCache(?CacheInterface $cache): void
    {
        self::$cache = $cache;
    }

    /**
     * Gets the implementation of cache that is being used for cell collection.
     */
    public static function getCache(): CacheInterface
    {
        if (!self::$cache) {
            self::$cache = self::useSimpleCacheVersion3() ? new Memory\SimpleCache3() : new Memory\SimpleCache1();
        }

        return self::$cache;
    }

    public static function useSimpleCacheVersion3(): bool
    {
<<<<<<< HEAD
        return (new ReflectionClass(CacheInterface::class))->getMethod('get')->getReturnType() !== null;
=======
        return
            PHP_MAJOR_VERSION === 8 &&
            (new ReflectionClass(CacheInterface::class))->getMethod('get')->getReturnType() !== null;
>>>>>>> main
    }

    /**
     * Set the HTTP client implementation to be used for network request.
     *
<<<<<<< HEAD
     * @deprecated 5.4.0 No replacement.
     *
     * @codeCoverageIgnore
     */
    public static function setHttpClient(mixed $httpClient, mixed $requestFactory): void
=======
     * @param mixed $httpClient
     * @param mixed $requestFactory
     *
     * @deprecated 1.30.2 No replacement.
     */
    public static function setHttpClient($httpClient, $requestFactory): void
>>>>>>> main
    {
        self::$httpClient = $httpClient;
        self::$requestFactory = $requestFactory;
    }

    /**
     * Unset the HTTP client configuration.
     *
<<<<<<< HEAD
     * @deprecated 5.4.0 No replacement.
     *
     * @codeCoverageIgnore
=======
     * @deprecated 1.30.2 No replacement.
>>>>>>> main
     */
    public static function unsetHttpClient(): void
    {
        self::$httpClient = null;
        self::$requestFactory = null;
    }

    /**
     * Get the HTTP client implementation to be used for network request.
     *
<<<<<<< HEAD
     * @deprecated 5.4.0 No replacement.
     *
     * @codeCoverageIgnore
     */
    public static function getHttpClient(): mixed
=======
     * @return mixed
     *
     * @deprecated 1.30.2 No replacement.
     */
    public static function getHttpClient()
>>>>>>> main
    {
        return self::$httpClient;
    }

    /**
     * Get the HTTP request factory.
     *
<<<<<<< HEAD
     * @deprecated 5.4.0 No replacement.
     *
     * @codeCoverageIgnore
     */
    public static function getRequestFactory(): mixed
=======
     * @return mixed
     *
     * @deprecated 1.30.2 No replacement.
     */
    public static function getRequestFactory()
>>>>>>> main
    {
        return self::$requestFactory;
    }
}
