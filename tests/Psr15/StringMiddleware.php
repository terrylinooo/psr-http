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

class StringMiddleware extends Middleware
{
    public function process(ServerRequestInterface  $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute('string', 'e04');

        return $handler->handle($request);
    }
}
