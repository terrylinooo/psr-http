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

/**
 * Server Request Factory
 */
class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        $data = SuperGlobal::extract();

        if (empty($method)) {
            $method = $data[0]['REQUEST_METHOD'] ?? 'GET';
        }

        $protocol = $data['server']['SERVER_PROTOCOL'] ?? '1.1';
        $protocol = str_replace('HTTP/', '',  $protocol);

        $uriFactory = new uriFactory();
        $streamFactory = new StreamFactory();

        $uri = $uriFactory->createUri($uri);
        $body = $streamFactory->createStream();

        return new ServerRequest(
            $method,
            $uri,
            $body,
            $data[5],  // header
            $protocol,
            $data[0],  // server
            $data[1],  // cookie
            $data[2],  // post
            $data[3],  // get
            $data[4]   // files
        );
    }
}
