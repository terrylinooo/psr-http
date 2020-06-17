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

namespace Shieldon\Psr15;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Shieldon\Psr7\Response;

/**
 * PSR-15 Middleware
 */
class RequestHandler implements RequestHandlerInterface
{
    /**
     * Queue of middlewares ready to run.
     *
     * @var array
     */
    protected $queue = [];

    /**
     * Add middlewares to the queue.
     *
     * @param MiddlewareInterface $middleware
     *
     * @return void
     */
    public function add(MiddlewareInterface $middleware)
    {
        $this->queue[] = $middleware;
    }
    
    /**
     * Process each middleware one by one.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (0 === count($this->queue)) {
            return new Response();
        }

        $middleware = array_shift($this->queue);

        return $middleware->process($request, $this);
    }
}