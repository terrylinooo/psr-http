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
use Psr\Http\Message\ResponseInterface;
use Shieldon\Psr17\ResponseFactory;

class ResponseFactoryTest extends TestCase
{
    public function test_createResponse()
    {
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse(200, 'OK');

        $this->assertTrue(($response instanceof ResponseInterface));
    }

    public function test_createNew()
    {
        $response = ResponseFactory::fromNew();

        $this->assertTrue(($response instanceof ResponseInterface));
    }
}
