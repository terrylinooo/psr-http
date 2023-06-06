<?php
/**
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Psr17;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Shieldon\Psr7\Uri;

/**
 * PSR-17 Uri Factory
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUri(string $uri = '') : UriInterface
    {
        return new Uri($uri);
    }

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Create a UriInterface instance from global variable.
     *
     * @return UriInterface
     */
    public static function fromGlobal(): UriInterface
    {
        $server = $_SERVER ?? [];

        $uri = '';
        $user = '';
        $host = '';
        $pass = '';
        $path = '';
        $port = '';
        $query = '';
        $scheme = '';

        $uriComponents = [
            'user' => 'PHP_AUTH_USER',
            'host' => 'HTTP_HOST',
            'pass' => 'PHP_AUTH_PW',
            'path' => 'REQUEST_URI',
            'port' => 'SERVER_PORT',
            'query' => 'QUERY_STRING',
            'scheme' => 'REQUEST_SCHEME',
        ];

        foreach ($uriComponents as $key => $value) {
            ${$key} = $server[$value] ?? '';
        }

        $userInfo = $user;

        if ($pass) {
            $userInfo .= ':' . $pass;
        }

        $authority = '';

        if ($userInfo) {
            $authority .= $userInfo . '@';
        }

        $authority .= $host;

        if ($port) {
            $authority .= ':' . $port;
        }

        if ($scheme) {
            $uri .= $scheme . ':';
        }

        if ($authority) {
            $uri .= '//' . $authority;
        }

        $uri .= '/' . ltrim($path, '/');

        if ($query) {
            $uri .= '?' . $query;
        }

        return new Uri($uri);
    }

    /**
     * Create a new URI.
     *
     * @return UriInterface
     */
    public static function fromNew(): UriInterface
    {
        return new Uri();
    }
}
