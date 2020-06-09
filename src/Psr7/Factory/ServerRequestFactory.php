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
use Shieldon\Psr7\Factory\StreamFactory;
use Shieldon\Psr7\Factory\UriFactory;
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

        $protocol = $server['SERVER_PROTOCOL'] ?? '1.1';
        $protocol = str_replace('HTTP/', '',  $protocol);

        $uriFactory = new UriFactory();
        $streamFactory = new StreamFactory();

        $uri = $uriFactory->createUri($uri);
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
}
