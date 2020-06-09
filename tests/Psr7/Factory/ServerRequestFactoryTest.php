<?php 
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Psr7\Factory;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Shieldon\Psr7\Factory\ServerRequestFactory;

class ServerRequestFactoryTest extends TestCase
{
    public function test_createServerRequest()
    {
        $serverRequestFactory = new ServerRequestFactory();
        $serverRequest = $serverRequestFactory->createServerRequest('GET', '', []);

        $this->assertTrue(($serverRequest instanceof ServerRequestInterface));
    }
}
