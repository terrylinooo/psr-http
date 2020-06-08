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

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Shieldon\Psr7\Uri;

/**
 * Uri Factory
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUri(string $uri = '') : UriInterface
    {
        return new Uri($uri);
    }
}
