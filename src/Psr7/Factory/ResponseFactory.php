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

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Shieldon\Psr7\Factory\StreamFactory;
use Shieldon\Psr7\Response;
use Shieldon\Psr7\Utils\SuperGlobal;

use function str_replace;

/**
 * Response Factory
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        $data = SuperGlobal::extract();

        $protocol = $data[0]['SERVER_PROTOCOL'] ?? '1.1';
        $protocol = str_replace('HTTP/', '',  $protocol);

        $streamFactory = new streamFactory();

        $body = $streamFactory->createStream();

        return new Response(
            $code,
            $data[5],
            $body,
            $protocol,
            $reasonPhrase
        );
    }
}
