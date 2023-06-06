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

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Shieldon\Psr17\StreamFactory;
use Shieldon\Psr17\Utils\SuperGlobal;
use Shieldon\Psr7\Response;

use function str_replace;
use function extract;

/**
 * PSR-17 Response Factory
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        extract(SuperGlobal::extract());

        $protocol = $server['SERVER_PROTOCOL'] ?? '1.1';
        $protocol = str_replace('HTTP/', '',  $protocol);

        $streamFactory = new streamFactory();

        $body = $streamFactory->createStream();

        return new Response(
            $code,
            $header, // from extract.
            $body,
            $protocol,
            $reasonPhrase
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new Response.
     *
     * @return ResponseInterface
     */
    public static function fromNew(): ResponseInterface
    {
        return new Response();
    }
}
