<?php 
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Test\Psr17;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Shieldon\Psr17\UriFactory;

class UriFactoryTest extends TestCase
{
    public function test_createUri()
    {
        $uriFactory = new UriFactory;

        $uri = $uriFactory->createUri();
        $this->assertTrue(($uri instanceof UriInterface));
    }
}
