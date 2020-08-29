<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Psr17\Utils;

use function microtime;
use function php_sapi_name;
use function str_replace;
use function strtolower;
use function substr;
use function time;

/**
 * Data Helper
 */
class SuperGlobal
{
    /**
     * Extract data from global variables.
     *
     * @return array
     */
    public static function extract(): array
    {
        if (php_sapi_name() === 'cli') {
            self::mockCliEnvironment();
        }

        // Here we add the HTTP prefix by ourselves...
        $headerParamsWithoutHttpPrefix = [
            'CONTENT_TYPE',
            'CONTENT_LENGTH',
        ];

        foreach ($headerParamsWithoutHttpPrefix as $value) {
            if (isset($_SERVER[$value])) {
                $_SERVER['HTTP_' . $value] = $_SERVER[$value];
            }
        }

        $headerParams = [];
        $serverParams = $_SERVER ?? [];
        $cookieParams = $_COOKIE ?? [];
        $filesParams = $_FILES ?? [];
        $postParams = $_POST ?? [];
        $getParams = $_GET ?? [];
        
        foreach ($serverParams as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $key = strtolower(str_replace('_', '-', substr($name, 5)));
                $headerParams[$key] = $value;
            }
        }

        return [
            'header' => $headerParams,
            'server' => $serverParams,
            'cookie' => $cookieParams,
            'files' => $filesParams,
            'post' => $postParams,
            'get' => $getParams,
        ];
    }

    // @codeCoverageIgnoreStart

    /**
     * Mock data for unit testing purpose ONLY.
     * 
     * @param array $server Overwrite the mock data.
     *
     * @return void
     */
    public static function mockCliEnvironment(array $server = []): void
    {
        $_POST   = $_POST   ?? [];
        $_COOKIE = $_COOKIE ?? [];
        $_GET    = $_GET    ?? [];
        $_FILES  = $_FILES  ?? [];
        $_SERVER = $_SERVER ?? [];

        $defaultServerParams = [
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9,zh-TW;q=0.8,zh;q=0.7',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'HTTP_HOST' => '127.0.0.1',
            'QUERY_STRING' => '',
            'REMOTE_ADDR' => '127.0.0.1',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_SCHEME' => 'http',
            'REQUEST_TIME' => time(),
            'REQUEST_TIME_FLOAT' => microtime(true),
            'REQUEST_URI' => '',
            'SCRIPT_NAME' => '',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'CONTENT_TYPE' => 'text/html; charset=UTF-8',
        ];

        if (defined('NO_MOCK_ENV')) {
           $defaultServerParams = [];
        }

        $_SERVER = array_merge($defaultServerParams, $_SERVER, $server);
    }

    // @codeCoverageIgnoreEnd
}
