<?php 
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Psr7;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Shieldon\Psr7\Uri;

use InvalidArgumentException;
use ReflectionObject;

class UriTest extends TestCase
{
    function test__construct()
    {
        $uri = new Uri('http://jack:1234@example.com/demo/?test=5678&test2=90#section-1');

        $this->assertTrue(($uri instanceof UriInterface));
    }

    function test__toString()
    {
        // Test 1

        $uri = new Uri('http://jack:1234@example.com:8888/demo/?test=5678&test2=90#section-1');

        ob_start();
        echo $uri;
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame('http://jack:1234@example.com:8888/demo/?test=5678&test2=90#section-1', $output);

        // Test 2

        $uri = new Uri('http://example.com:8888/demo/#section-1');

        ob_start();
        echo $uri;
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame('http://example.com:8888/demo/#section-1', $output);
    }

    function testProperties()
    {
        $uri = new Uri('http://jack:1234@example.com:8080/demo/?test=5678&test2=90#section-1');

        $components = [
            'scheme', 
            'user', 
            'pass',
            'host',
            'port',
            'path', 
            'query', 
            'fragment',
        ];

        $reflection = new ReflectionObject($uri);

        foreach($components as $v) {
            $tmp = $reflection->getProperty($v);
            $tmp->setAccessible(true);
            ${$v} = $tmp->getValue($uri);
            unset($tmp);
        }

        $this->assertSame($scheme, 'http');
        $this->assertSame($host, 'example.com');
        $this->assertSame($user, 'jack');
        $this->assertSame($pass, '1234');
        $this->assertSame($path, '/demo/');
        $this->assertSame($port, 8080);
        $this->assertSame($query, 'test=5678&test2=90');
        $this->assertSame($fragment, 'section-1');
    }

    function testGetSeriesMethods()
    {
        // Test 1

        $uri = new Uri('http://jack:1234@example.com:8080/demo/?test=5678&test2=90#section-1');

        $this->assertSame($uri->getScheme(), 'http');
        $this->assertSame($uri->getHost(), 'example.com');
        $this->assertSame($uri->getUserInfo(), 'jack:1234');
        $this->assertSame($uri->getPath(), '/demo/');
        $this->assertSame($uri->getPort(), 8080);
        $this->assertSame($uri->getQuery(), 'test=5678&test2=90');
        $this->assertSame($uri->getFragment(), 'section-1');

        // Test 2

        $uri = new Uri('https://www.example.com');

        $this->assertSame($uri->getScheme(), 'https');
        $this->assertSame($uri->getHost(), 'www.example.com');
        $this->assertSame($uri->getUserInfo(), ''); // string
        $this->assertSame($uri->getPath(), '');     // string
        $this->assertSame($uri->getPort(), null);   // int|null
        $this->assertSame($uri->getQuery(), '');    // string
        $this->assertSame($uri->getFragment(), ''); // string
    }

    function testWithSeriesMethods()
    {
        $uri = new Uri('https://www.example.com');

        // Test 1

        $newUri = $uri->withScheme('http')
            ->withHost('example.com')
            ->withPort(8080)
            ->withUserInfo('jack', '4321')
            ->withPath('/en')
            ->withQuery('test=123')
            ->withFragment('1234');
        
        $this->assertSame($newUri->getScheme(), 'http');
        $this->assertSame($newUri->getHost(), 'example.com');
        $this->assertSame($newUri->getUserInfo(), 'jack:4321');
        $this->assertSame($newUri->getPath(), '/en');
        $this->assertSame($newUri->getPort(), 8080);
        $this->assertSame($newUri->getQuery(), 'test=123');
        $this->assertSame($newUri->getFragment(), '1234');

        unset($newUri);

        // Test 2

        $newUri = $uri->withScheme('http')
            ->withHost('freedom.com')
            ->withPort(80)
            ->withUserInfo('people')
            ->withPath('/天安門')
            ->withQuery('chineseChars=六四')
            ->withFragment('19890604');
        
        $this->assertSame($newUri->getScheme(), 'http');
        $this->assertSame($newUri->getHost(), 'freedom.com');
        $this->assertSame($newUri->getUserInfo(), 'people');
        $this->assertSame($newUri->getPath(), '/%25E5%25A4%25A9%25E5%25AE%2589%25E9%2596%2580');
        $this->assertSame($newUri->getPort(), 80);
        $this->assertSame($newUri->getQuery(), 'chineseChars=%E5%85%AD%E5%9B%9B');
        $this->assertSame($newUri->getFragment(), '19890604');
    }

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */

    function testAssertScheme()
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = new Uri();

        $reflection = new ReflectionObject($uri);
        $assertScheme = $reflection->getMethod('assertScheme');
        $assertScheme->setAccessible(true);
        $assertScheme->invokeArgs($uri, ['telnet']);
    }

    function testAssertString()
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = new Uri();

        $reflection = new ReflectionObject($uri);
        $assertString = $reflection->getMethod('assertString');
        $assertString->setAccessible(true);
        $assertString->invokeArgs($uri, [1234]);
    }

    function testAssertValidUri()
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = new Uri();

        $reflection = new ReflectionObject($uri);
        $assertValidUri = $reflection->getMethod('assertValidUri');
        $assertValidUri->setAccessible(true);
        $assertValidUri->invokeArgs($uri, ['https://www.example_test.com']);
    }

    function testAssertHost()
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = new Uri();

        $reflection = new ReflectionObject($uri);
        $assertHost = $reflection->getMethod('assertHost');
        $assertHost->setAccessible(true);
        $assertHost->invokeArgs($uri, ['example_test.com']);
    }

    function testAssertHostReturnVoid()
    {
        $uri = new Uri();

        $reflection = new ReflectionObject($uri);
        $assertHost = $reflection->getMethod('assertHost');
        $assertHost->setAccessible(true);
        $result = $assertHost->invokeArgs($uri, ['']);

        $this->assertSame($result, null);
    }

    function testAssertPortInvalidVariableType()
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = new Uri();

        $reflection = new ReflectionObject($uri);
        $assertPort = $reflection->getMethod('assertPort');
        $assertPort->setAccessible(true);
        $assertPort->invokeArgs($uri, ['8080']);
    }

    function testAssertPortInvalidRangeNumer()
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = new Uri();

        $reflection = new ReflectionObject($uri);
        $assertPort = $reflection->getMethod('assertPort');
        $assertPort->setAccessible(true);
        $assertPort->invokeArgs($uri, [70000]);
    }
}
