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
use Psr\Http\Message\RequestInterface;
use Shieldon\Psr17\RequestFactory;

class RequestFactoryTest extends TestCase
{
    public function test_createRequest()
    {
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest('POST', 'https://www.google.com');

        $this->assertTrue(($request instanceof RequestInterface));
    }

    public function test_createNew()
    {
        $request = RequestFactory::fromNew();

        $this->assertTrue(($request instanceof RequestInterface));
    }
}
