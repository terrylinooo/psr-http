<?php 
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Test\Psr15;

use PHPUnit\Framework\TestCase;
use Shieldon\Psr15\RequestHandler;
use Shieldon\Psr17\ServerRequestFactory;
use Shieldon\Psr17\Utils\SuperGlobal;
use Shieldon\Test\Psr15\ApiMiddleware;
use Shieldon\Test\Psr15\StringMiddleware;

class RequestHandlerTest extends TestCase
{
    public function test_requestHandler()
    {
        $app = new RequestHandler();

        $app->add(new ApiMiddleware());
        $app->add(new StringMiddleware());

        $response = $app->handle(ServerRequestFactory::fromGlobal());

        $this->assertEquals(406, $response->getStatusCode());
        $this->assertEquals('', $response->getBody()->getContents());
    }

    public function test_requestHandler_Condition_2()
    {
        $finalHandler = new FinalHandler();

        $app = new RequestHandler($finalHandler);

        $app->add(new ApiMiddleware());
        $app->add(new StringMiddleware());

        $request = ServerRequestFactory::fromGlobal();

        $request = $request->withHeader('Content-Type', 'application/json')->
            withHeader('key', '23492834234')->
            withHeader('secret', '1a163782ee166156294d173fcf8b8e87');


        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('e04su3su;6', $response->getBody()->getContents());
    }

    public function test_requestHandler_Condition_3()
    {
        // Without a fallback handler...
        $app = new RequestHandler();

        $app->add(new ApiMiddleware());
        $app->add(new StringMiddleware());

        $request = ServerRequestFactory::fromGlobal();

        $request = $request->withHeader('Content-Type', 'application/json')->
            withHeader('key', '23492834234')->
            withHeader('secret', '1a163782ee166156294d173fcf8b8e87');


        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', $response->getBody()->getContents());
    }
}
