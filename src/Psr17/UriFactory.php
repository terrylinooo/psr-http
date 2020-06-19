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

        $userInfo = $user ? $user . ($pass ? ":{$pass}" : '') : '';

        $authority = ($userInfo ? "{$userInfo}@": '') 
            . $host
            . ($port ? ":{$port}" : '');

        $uri = ($scheme ? "{$scheme}:" : '')
            . ($authority ? "//{$authority}" : '')
            . '/'
            . ltrim($path, '/')
            . ($query ? "?{$query}" : '');

        return new Uri($uri);
    }
}
