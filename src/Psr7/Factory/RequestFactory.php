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

namespace Shieldon\Psr7;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Shieldon\Psr7\Factory\UriFactory;
use Shieldon\Psr7\Factory\StreamFactory;
use Shieldon\Psr7\Utils\SuperGlobal;

use function str_replace;

/**
 * Request Factory
 */
class RequestFactory implements RequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        $data = SuperGlobal::extract();

        if (empty($method)) {
            $method = $data[0]['REQUEST_METHOD'] ?? 'GET';
        }

        $protocol = $data[0]['SERVER_PROTOCOL'] ?? '1.1';
        $protocol = str_replace('HTTP/', '',  $protocol);

        $uriFactory = new uriFactory();
        $streamFactory = new StreamFactory();

        $uri = $uriFactory->createUri($uri);
        $body = $streamFactory->createStream();

        return Request(
            $method,
            $uri,
            $body,
            $data[5],
            $protocol
        );
    }
}
