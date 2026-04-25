<?php

namespace PhpOffice\PhpSpreadsheet\Calculation\Web;

use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\Calculation\Information\ExcelError;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
<<<<<<< HEAD
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
=======
>>>>>>> main

class Service
{
    /**
     * WEBSERVICE.
     *
     * Returns data from a web service on the Internet or Intranet.
     *
     * Excel Function:
     *        Webservice(url)
     *
<<<<<<< HEAD
     * @return string the output resulting from a call to the webservice
     */
    public static function webService(mixed $url, ?Cell $cell = null): ?string
=======
     * @param mixed $url
     *
     * @return ?string the output resulting from a call to the webservice
     */
    public static function webService($url, ?Cell $cell = null)
>>>>>>> main
    {
        if (is_array($url)) {
            $url = Functions::flattenSingleValue($url);
        }
<<<<<<< HEAD
        $url = trim(StringHelper::convertToString($url, false));
=======
        if (!is_string($url)) {
            return ExcelError::VALUE(); // Invalid URL length
        }
        $url = trim($url);
>>>>>>> main
        if (mb_strlen($url) > 2048) {
            return ExcelError::VALUE(); // Invalid URL length
        }
        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? '';
        if ($scheme !== 'http' && $scheme !== 'https') {
            return ExcelError::VALUE(); // Invalid protocol
        }
<<<<<<< HEAD
        $domainWhiteList = $cell?->getWorksheet()->getParent()?->getDomainWhiteList() ?? [];
        $host = $parsed['host'] ?? '';
        if (!in_array($host, $domainWhiteList, true)) {
            return ($cell === null) ? null : Functions::NOT_YET_IMPLEMENTED; // will be converted to oldCalculatedValue or null
=======
        $domainWhiteList = [];
        if ($cell !== null) {
            $parent = $cell->getWorksheet()->getParent();
            if ($parent !== null) {
                $domainWhiteList = $parent->getDomainWhiteList();
            }
        }
        $host = $parsed['host'] ?? '';
        if (!in_array($host, $domainWhiteList, true)) {
            return ($cell === null) ? null : '#Not Yet Implemented'; // will be converted to oldCalculatedValue or null
>>>>>>> main
        }

        // Get results from the webservice
        $ctxArray = [
            'http' => [
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            ],
        ];
        if ($scheme === 'https') {
            $ctxArray['ssl'] = ['crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT];
        }
        $ctx = stream_context_create($ctxArray);
        $output = @file_get_contents($url, false, $ctx);
        if ($output === false || mb_strlen($output) > 32767) {
            return ExcelError::VALUE(); // Output not a string or too long
        }

        return $output;
    }

    /**
     * URLENCODE.
     *
     * Returns data from a web service on the Internet or Intranet.
     *
     * Excel Function:
     *        urlEncode(text)
     *
<<<<<<< HEAD
     * @return string the url encoded output
     */
    public static function urlEncode(mixed $text): string
=======
     * @param mixed $text
     *
     * @return string the url encoded output
     */
    public static function urlEncode($text)
>>>>>>> main
    {
        if (!is_string($text)) {
            return ExcelError::VALUE();
        }

        return str_replace('+', '%20', urlencode($text));
    }
}
