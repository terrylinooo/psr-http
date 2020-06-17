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
use Psr\Http\Message\ServerRequestInterface;
use Shieldon\Psr17\ServerRequestFactory;
use Shieldon\Psr17\Utils\SuperGlobal;

class ServerRequestFactoryTest extends TestCase
{
    public function test_createServerRequest()
    {
        SuperGlobal::mockCliEnvironment();

        $serverRequestFactory = new ServerRequestFactory();
        $serverRequest = $serverRequestFactory->createServerRequest('GET', '', $_SERVER);

        $this->assertTrue(($serverRequest instanceof ServerRequestInterface));
    }

    public function test_createServerRequestFromGlobal()
    {
        SuperGlobal::mockCliEnvironment([
            'PHP_AUTH_USER' => 'terry',
            'PHP_AUTH_PW' => '1234',
            'QUERY_STRING' => 'foo=bar'
        ]);

        $serverRequest = ServerRequestFactory::fromGlobal();

        $this->assertTrue(($serverRequest instanceof ServerRequestInterface));
    }
}
