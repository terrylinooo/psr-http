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
use Shieldon\Psr17\Utils\SuperGlobal;

class UriFactoryTest extends TestCase
{
    public function test_createUri()
    {
        $uriFactory = new UriFactory;

        $uri = $uriFactory->createUri();
        $this->assertTrue(($uri instanceof UriInterface));
    }

    public function test_fromGlobal()
    {
        SuperGlobal::mockCliEnvironment([
            'PHP_AUTH_USER'  => 'terry',       // user
            'HTTP_HOST'      => 'example.org', // host
            'PHP_AUTH_PW'    => '1234',        // pass
            'REQUEST_URI'    => '/test',       // path
            'SERVER_PORT'    => '8080',        // port
            'QUERY_STRING'   => 'foo=bar',     // query
            'REQUEST_SCHEME' => 'https',       // scheme
        ]);

        $uri = uriFactory::fromGlobal();

        $this->assertSame($uri->getScheme(), 'https');
        $this->assertSame($uri->getHost(), 'example.org');
        $this->assertSame($uri->getUserInfo(), 'terry:1234');  // string
        $this->assertSame($uri->getPath(), '/test');           // string
        $this->assertSame($uri->getPort(), 8080);              // int|null
        $this->assertSame($uri->getQuery(), 'foo=bar');        // string
        $this->assertSame($uri->getFragment(), '');            // string
    }

    public function test_fromNew()
    {
        $uri = uriFactory::fromNew();
        $this->assertTrue(($uri instanceof UriInterface));
    }
}
