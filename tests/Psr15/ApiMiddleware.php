<?php 
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Test\Psr15;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Shieldon\Psr15\Middleware;
use Shieldon\Psr7\Response;

class ApiMiddleware extends Middleware
{
    public function process(ServerRequestInterface  $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');
        $key = $request->getHeaderLine('key');
        $secret = $request->getHeaderLine('secret');

        if ($contentType !== 'application/json') {
            return (new Response)->withStatus(406, 'Content type is not accepted.');
        }

        if ($key !== '23492834234') {
            return (new Response)->withStatus(401, 'API key is invalid.');
        }

        if ($secret !== '1a163782ee166156294d173fcf8b8e87') {
            return (new Response)->withStatus(401, 'API secret is invalid.');
        }

        return $handler->handle($request);
    }
}
