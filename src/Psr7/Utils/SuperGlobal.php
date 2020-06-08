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

namespace Shieldon\Psr7\Utils;

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
        $serverParams = $_SERVER ?? [];
        $cookieParams = $_COOKIE ?? [];
        $postParams = $_POST ?? [];
        $getParams = $_GET ?? [];
        $filesParams = $_FILES ?? [];
        $headerParams = [];

        foreach ($serverParams as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $key = strtolower(str_replace('_', '-', substr($name, 5)));
                $headerParams[$key] = $value;
            }
        }

        return [
            0 => $serverParams,
            1 => $cookieParams,
            2 => $postParams,
            3 => $getParams,     
            4 => $filesParams,
            5 => $headerParams,
        ];
    }
}
