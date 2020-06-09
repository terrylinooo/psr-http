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
use Psr\Http\Message\RequestInterface;
use Shieldon\Psr7\Factory\RequestFactory;

class RequestFactoryTest extends TestCase
{
    public function test_createRequest()
    {
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest('POST', 'https://www.google.com');

        $this->assertTrue(($request instanceof RequestInterface));
    }
}
