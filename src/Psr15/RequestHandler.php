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

namespace Shieldon\Psr15;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Shieldon\Psr7\Response;

/**
 * PSR-15 Middleware
 */
class RequestHandler implements RequestHandlerInterface
{
    /**
     * Middlewares in the queue are ready to run.
     *
     * @var array
     */
    protected $queue = [];

    /**
     * After the last middleware has been called, a fallback handler should
     * parse the request and give an appropriate response.
     *
     * @var RequestHandlerInterface|null
     */
    protected $fallbackHandler = null;

    /**
     * RequestHandler constructor.
     * 
     * @param RequestHandler|null $finalRequestHandler A valid resource.
     */
    public function __construct(?RequestHandlerInterface $fallbackHandler = null)
    {
        if ($fallbackHandler instanceof RequestHandlerInterface) {
            $this->fallbackHandler = $fallbackHandler;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(MiddlewareInterface $middleware)
    {
        $this->queue[] = $middleware;
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (0 === count($this->queue)) {
            return $this->final($request);
        }

        $middleware = array_shift($this->queue);

        return $middleware->process($request, $this);
    }

    /**
     * This is the final, there is no middleware needed to execute, pasre the 
     * layered request and give a parsed response.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    protected function final(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->fallbackHandler) {
            return new Response();
        }

        return $this->fallbackHandler->handle($request);
    }
}
