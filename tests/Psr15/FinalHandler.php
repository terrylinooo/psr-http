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

namespace Shieldon\Test\Psr15;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Shieldon\Psr15\RequestHandler;
use Shieldon\Psr7\Response;

/**
 * PSR-15 Middleware
 */
class FinalHandler extends RequestHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response();

        if (!empty($request->getAttribute('string'))) {
 
            $response = $response->withStatus(200, 'OK');
            $response->getBody()->write('e04su3su;6');
            $response->getBody()->rewind();
        }

        return $response;
    }
}