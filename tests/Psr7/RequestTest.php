<?php 
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Test\Psr7;

use PHPUnit\Framework\TestCase;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\UriInterface;
use Shieldon\Psr7\Request;
use Shieldon\Psr7\Stream;
use Shieldon\Psr7\Uri;
use InvalidArgumentException;

class RequestTest extends TestCase
{
    public function test__construct()
    {
        $request = new Request('GET', '', '', [], '1.1');

        $this->assertTrue(($request instanceof RequestInterface));
        $this->assertTrue(($request instanceof MessageInterface));

        $uri = new Uri('https://www.example.com');
        $request = new Request('GET', $uri, '', [], '1.1');

        $this->assertSame($request->getUri()->getHost(), 'www.example.com');
    }

    public function test_GetPrefixMethods()
    {
        // Test 1

        $request = new Request('POST', 'https://terryl.in/zh/?test=test', '', ['test' => 1234], '1.1');

        $this->assertSame($request->getRequestTarget(), '/zh/?test=test');
        $this->assertSame($request->getMethod(), 'POST');

        // Let's double check the Uri instance again.
        $this->assertTrue(($request->getUri() instanceof UriInterface));
        $this->assertSame($request->getUri()->getScheme(), 'https');
        $this->assertSame($request->getUri()->getHost(), 'terryl.in');
        $this->assertSame($request->getUri()->getUserInfo(), '');
        $this->assertSame($request->getUri()->getPath(), '/zh/');
        $this->assertSame($request->getUri()->getPort(), null);
        $this->assertSame($request->getUri()->getQuery(), 'test=test');
        $this->assertSame($request->getUri()->getFragment(), '');

        // Test 2

        $request = new Request('GET', 'https://terryl.in', '', [], '1.1');

        $this->assertSame($request->getRequestTarget(), '/');
    }

    public function test_WithPrefixMethods()
    {
        $request = new Request('GET', 'https://terryl.in/zh/', '', [], '1.1');

        $newRequest = $request->withMethod('POST')->withUri(new Uri('https://play.google.com'));

        $this->assertSame($newRequest->getMethod(), 'POST');
        $this->assertSame($newRequest->getRequestTarget(), '/');
        $this->assertSame($newRequest->getUri()->getHost(), 'play.google.com');

        $new2Request = $newRequest->withRequestTarget('/newTarget/test/?q=1234');
        $this->assertSame($new2Request->getRequestTarget(), '/newTarget/test/?q=1234');

        $new3Request = $new2Request->withUri(new Uri('https://www.facebook.com'), true);

        // Preserve Host
        $this->assertSame($new3Request->getHeaderLine('host'), 'play.google.com');
        $this->assertSame($new3Request->getUri()->getHost(), 'www.facebook.com');
    }

    public function test_setBody()
    {
        $resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
        $stream = new Stream($resource);

        $request = new Request('POST', 'https://terryl.in/zh/', $stream, [], '1.1');
        $this->assertEquals($request->getBody(), $stream);

        $request = new Request('POST', 'https://terryl.in/zh/', 'test stream', [], '1.1');
        $this->assertEquals(sprintf('%s', $request->getBody()->getContents()), 'test stream');
    }

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */

    public function test_Exception_assertMethod_1()
    {
        $this->expectException(InvalidArgumentException::class);
        $request = new Request('GET', 'https://terryl.in/', '', [], '1.1');


        // Exception:
        // => HTTP method must be a string.
        $newRequest = $request->withMethod(['POST']);
    }

    public function test_Exception_assertMethod_2()
    {
        $this->expectException(InvalidArgumentException::class);

        // Exception:
        // => Unsupported HTTP method. It must be compatible with RFC-7231 
        //    request method, but "GETX" provided.
        $request = new Request('GETX', 'https://terryl.in/', '', [], '1.1');
    }

    public function test_Exception_assertProtocolVersion()
    {
        $this->expectException(InvalidArgumentException::class);

        // Exception:
        // => Unsupported HTTP protocol version number. "1.5" provided.
        $request = new Request('GET', 'https://terryl.in/', '', [], '1.5');
    }

    public function test_Exception_withRequestTarget_ContainSpaceCharacter()
    {
        $this->expectException(InvalidArgumentException::class);

        $request = new Request('GET', 'https://terryl.in/', '', [], '1.1');

        // Exception:
        // => A request target cannot contain any whitespace.
        $newRequest = $request->withRequestTarget('/newTarget/te st/?q=1234');
    }

    public function test_Exception_withRequestTarget_InvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        $request = new Request('GET', 'https://terryl.in/', '', [], '1.1');

        // Exception:
        // => A request target must be a string.
        $newRequest = $request->withRequestTarget(['foo' => 'bar']);
    }

    public function test_Exception_Constructor()
    {
        $this->expectException(InvalidArgumentException::class);

        // Exception:
        // => URI should be a string or an instance of UriInterface, but array provided.
        $request = new Request('GET', [], '', [], '1.1');
    }
}
