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

namespace Shieldon\Psr7\Factory;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Shieldon\Psr7\Factory\StreamFactory;
use Shieldon\Psr7\Factory\UriFactory;
use Shieldon\Psr7\Uri;
use Shieldon\Psr7\ServerRequest;
use Shieldon\Psr7\Utils\SuperGlobal;

use function str_replace;
use function extract;

/**
 * PSR-17 Server Request Factory
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        extract(SuperGlobal::extract());

        if ($serverParams !== []) {
            $server = $serverParams;
        }

        $protocol = $server['SERVER_PROTOCOL'] ?? '1.1';
        $protocol = str_replace('HTTP/', '',  $protocol);

        if (! ($uri instanceof UriInterface)) {
            $uriFactory = new UriFactory();
            $uri = $uriFactory->createUri($uri);
        }

        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream();

        return new ServerRequest(
            $method,
            $uri,
            $body,
            $header, // from extract.
            $protocol,
            $server, // from extract.
            $cookie, // from extract.
            $post,   // from extract.
            $get,    // from extract.
            $files   // from extract.
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Create a ServerRequestInterface instance from global variable.
     *
     * @return ServerRequestInterface
     */
    public static function createServerRequestFromGlobal(): ServerRequestInterface
    {
        extract(SuperGlobal::extract());

        // HTTP method.
        $method = $server['REQUEST_METHOD'] ?? 'GET';

        // HTTP protocal version.
        $protocol = $server['SERVER_PROTOCOL'] ?? '1.1';
        $protocol = str_replace('HTTP/', '',  $protocol);

        $uri = self::createUriFromGlobal();

        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream();

        return new ServerRequest(
            $method,
            $uri,
            $body,
            $header, // from extract.
            $protocol,
            $server, // from extract.
            $cookie, // from extract.
            $post,   // from extract.
            $get,    // from extract.
            $files   // from extract.
        );
    }

    /**
     * Create a UriInterface instance from global variable.
     *
     * @return UriInterface
     */
    public function createUriFromGlobal(): UriInterface
    {
        $server = $_SERVER ?? [];

        $uri = '';

        $uriComponents = [
            'host' => 'HTTP_HOST',
            'pass' => 'PHP_AUTH_PW',
            'path' => 'REQUEST_URI',
            'port' => 'SERVER_PORT',
            'query' => 'QUERY_STRING',
            'scheme' => 'REQUEST_SCHEME',
            'user' => 'PHP_AUTH_USER',
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
}
