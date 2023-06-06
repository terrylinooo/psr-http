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

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-15 Middleware
 */
abstract class Middleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    abstract function process(ServerRequestInterface  $request,RequestHandlerInterface $handler): ResponseInterface;
}
