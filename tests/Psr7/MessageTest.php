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

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use ReflectionObject;
use Shieldon\Psr7\Message;
use Shieldon\Psr7\Stream;
use stdClass;

class MessageTest extends TestCase
{
    public function test__construct()
    {
        $message = new Message();

        $this->assertTrue(($message instanceof MessageInterface));
    }

    public function test_GetPrefixMethods()
    {
        $message = $this->test_setHeaders();

        $this->assertSame($message->getProtocolVersion(), '1.1');
        $this->assertEquals($message->getHeader('user-agent'), ['Mozilla/5.0 (Windows NT 10.0; Win64; x64)']);
        $this->assertEquals($message->getHeader('header-not-exists'), []);
        $this->assertEquals($message->getHeaderLine('user-agent'), 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

        // Test - has
        $this->assertTrue($message->hasHeader('user-agent'));
    }

    public function test_WithPrefixMethods()
    {
        $message = $this->test_setHeaders();
        $newMessage = $message->withProtocolVersion('2.0')->withHeader('hello-world', 'ok');
        $this->assertSame($newMessage->getProtocolVersion(), '2.0');
        $this->assertEquals($newMessage->getHeader('hello-world'), ['ok']);

        $new2Message = $newMessage
            ->withAddedHeader('hello-world', 'not-ok')
            ->withAddedHeader('foo-bar', 'okok')
            ->withAddedHeader('others', 2)
            ->withAddedHeader('others', 6.4);

        $this->assertEquals($new2Message->getHeader('hello-world'), ['ok', 'not-ok']);
        $this->assertEquals($new2Message->getHeader('foo-bar'), ['okok']);
        $this->assertEquals($new2Message->getHeader('others'), ['2', '6.4']);

        // Test - without
        $new3Message = $new2Message->withoutHeader('hello-world');
        $this->assertFalse($new3Message->hasHeader('hello-world'));
    }

    public function test_bodyMethods()
    {
        $resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
        $stream = new Stream($resource);

        $message = new Message();
        $newMessage = $message->withBody($stream);
        $this->assertEquals($newMessage->getBody(), $stream);
    }

    public function test_setHeaders(): MessageInterface
    {
        $message = new Message();

        $testArray = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Custom-Value' => '1234',
        ];

        $expectedArray = [
            'User-Agent' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64)'],
            'Custom-Value' => ['1234'],
        ];

        $reflection = new ReflectionObject($message);
        $setHeaders = $reflection->getMethod('setHeaders');
        $setHeaders->setAccessible(true);
        $setHeaders->invokeArgs($message, [$testArray]);

        $this->assertEquals($message->getHeaders(), $expectedArray);

        return $message;
    }

    public function test_Static_ParseRawHeader()
    {
        // Test 1 - General request header.
        $rawHeader =<<<EOF
        Accept: */*
        Content-Type: application/x-www-form-urlencoded;charset=UTF-8
        Sec-Fetch-Dest: empty
        Sec-Fetch-Mode: cors
        Sec-Fetch-Site: same-site
        User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36
        X-Client-Data: CJC2yQEIorbJAQjBtskBCKmdygEIqLTKARibvsoB
EOF;
        $headers = Message::parseRawHeader($rawHeader);

        $this->assertSame($headers['Accept'], '*/*');
        $this->assertSame($headers['Content-Type'], 'application/x-www-form-urlencoded;charset=UTF-8');
        $this->assertSame($headers['Sec-Fetch-Dest'], 'empty');
        $this->assertSame($headers['Sec-Fetch-Mode'], 'cors');
        $this->assertSame($headers['Sec-Fetch-Site'], 'same-site');
        $this->assertSame($headers['User-Agent'], 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36');
        $this->assertSame($headers['X-Client-Data'], 'CJC2yQEIorbJAQjBtskBCKmdygEIqLTKARibvsoB');

        // Test - General response header.
        $rawHeader =<<<EOF
        HTTP/1.1 200
        access-control-allow-credentials: true
        access-control-allow-methods: OPTIONS
        access-control-allow-origin: https://www.facebook.com
        access-control-expose-headers: X-FB-Debug, X-Loader-Length
        alt-svc: h3-27=":443"; ma=3600
        cache-control: private, no-cache, no-store, must-revalidate
        content-length: 0
        content-security-policy: default-src * data: blob: 'self';script-src *.facebook.com *.fbcdn.net *.facebook.net *.google-analytics.com *.virtualearth.net *.google.com 127.0.0.1:* *.spotilocal.com:* 'unsafe-inline' 'unsafe-eval' blob: data: 'self';style-src data: blob: 'unsafe-inline' *;connect-src *.facebook.com facebook.com *.fbcdn.net *.facebook.net *.spotilocal.com:* wss://*.facebook.com:* https://fb.scanandcleanlocal.com:* attachment.fbsbx.com ws://localhost:* blob: *.cdninstagram.com 'self' chrome-extension://boadgeojelhgndaghljhdicfkmllpafd chrome-extension://dliochdbjfkdbacpmhlcpmleaejidimm;block-all-mixed-content;upgrade-insecure-requests;
        content-type: text/html; charset="utf-8"
        date: Thu, 04 Jun 2020 02:46:12 GMT
        date: Thu, 04 Jun 2020 02:46:12 GMT
        expires: Sat, 01 Jan 2000 00:00:00 GMT
        pragma: no-cache
        status: 200
        strict-transport-security: max-age=15552000; preload
        vary: Origin
        x-content-type-options: nosniff
        x-fb-debug: XNgyVH3VRKeOuGyxAit2WqKJ+334baHQuKQP0CM2lr/8ToZmwhNFU9N5ctr3LeTgXYWXemfGlJaAl/PASeEL5Q==
        x-frame-options: DENY
        x-xss-protection: 0
EOF;
        $headers = Message::parseRawHeader($rawHeader);

        $this->assertSame($headers['access-control-allow-credentials'], 'true');
        $this->assertSame($headers['access-control-allow-methods'], 'OPTIONS');
        $this->assertSame($headers['access-control-allow-origin'], 'https://www.facebook.com');
        $this->assertSame($headers['access-control-expose-headers'], 'X-FB-Debug, X-Loader-Length');
        $this->assertSame($headers['alt-svc'], 'h3-27=":443"; ma=3600');
        $this->assertSame($headers['cache-control'], 'private, no-cache, no-store, must-revalidate');
        $this->assertSame($headers['content-length'], '0');
        $this->assertSame($headers['content-security-policy'], "default-src * data: blob: 'self';script-src *.facebook.com *.fbcdn.net *.facebook.net *.google-analytics.com *.virtualearth.net *.google.com 127.0.0.1:* *.spotilocal.com:* 'unsafe-inline' 'unsafe-eval' blob: data: 'self';style-src data: blob: 'unsafe-inline' *;connect-src *.facebook.com facebook.com *.fbcdn.net *.facebook.net *.spotilocal.com:* wss://*.facebook.com:* https://fb.scanandcleanlocal.com:* attachment.fbsbx.com ws://localhost:* blob: *.cdninstagram.com 'self' chrome-extension://boadgeojelhgndaghljhdicfkmllpafd chrome-extension://dliochdbjfkdbacpmhlcpmleaejidimm;block-all-mixed-content;upgrade-insecure-requests;");
        $this->assertSame($headers['content-type'], 'text/html; charset="utf-8"');
        $this->assertSame($headers['date'], 'Thu, 04 Jun 2020 02:46:12 GMT');
        $this->assertSame($headers['date'], 'Thu, 04 Jun 2020 02:46:12 GMT');
        $this->assertSame($headers['expires'], 'Sat, 01 Jan 2000 00:00:00 GMT');
        $this->assertSame($headers['pragma'], 'no-cache');
        $this->assertSame($headers['status'], '200');
        $this->assertSame($headers['strict-transport-security'], 'max-age=15552000; preload');
        $this->assertSame($headers['vary'], 'Origin');
        $this->assertSame($headers['x-content-type-options'], 'nosniff');
        $this->assertSame($headers['x-fb-debug'], 'XNgyVH3VRKeOuGyxAit2WqKJ+334baHQuKQP0CM2lr/8ToZmwhNFU9N5ctr3LeTgXYWXemfGlJaAl/PASeEL5Q==');
        $this->assertSame($headers['x-frame-options'], 'DENY');
        $this->assertSame($headers['x-xss-protection'], '0');

        // Test 3 - Just one line.
        $rawHeader =<<<EOF
        HTTP/1.1 200
        access-control-allow-credentials: true
EOF;
        $headers = Message::parseRawHeader($rawHeader);
        $this->assertEquals(count($headers), 1);
        $this->assertSame($headers['access-control-allow-credentials'], 'true');

        // Test 4 - Empty.
        $rawHeader = '';
        $headers = Message::parseRawHeader($rawHeader);
        $this->assertSame($headers, []);
    }

    public function test_WithAddedHeaderArrayValueAndKeys()
    {
        $message = new Message();
        $message = $message->withAddedHeader('content-type', ['foo' => 'text/html']);
        $message = $message->withAddedHeader('content-type', ['foo' => 'text/plain', 'bar' => 'application/json']);

        $headerLine = $message->getHeaderLine('content-type');
        $this->assertMatchesRegularExpression('|text/html|', $headerLine);
        $this->assertMatchesRegularExpression('|text/plain|', $headerLine);
        $this->assertMatchesRegularExpression('|application/json|', $headerLine);

        $message = $message->withAddedHeader('foo', '');
        $headerLine = $message->getHeaderLine('foo');
        $this->assertSame('', $headerLine);
    }

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */

    public function test_Exception_AssertHeaderFieldName()
    {
        $this->expectException(InvalidArgumentException::class);

        $message = new Message();

        // Exception:
        // => "hello-wo)rld" is not valid header name, it must be an RFC 7230 compatible string.
        $newMessage = $message->withHeader('hello-wo)rld', 'ok');
    }

    public function test_Exception_AssertHeaderFieldName_2()
    {
        $this->expectException(InvalidArgumentException::class);

        $message = new Message();

        // Exception:
        // => "hello-wo)rld" is not valid header name, it must be an RFC 7230 compatible string.
        $newMessage = $message->withHeader(['test'], 'ok');
    }

    public function test_Exception_AssertHeaderFieldValue_Booolean()
    {
        $this->expectException(InvalidArgumentException::class);

        $message = new Message();

        // Exception:
        // => The header field value only accepts string and array, but "boolean" provided.
        $newMessage = $message->withHeader('hello-world', false);
    }

    public function test_Exception_AssertHeaderFieldValue_Null()
    {
        $this->expectException(InvalidArgumentException::class);

        $message = new Message();

        // Exception:
        // => The header field value only accepts string and array, but "NULL" provided.
        $newMessage = $message->withHeader('hello-world', null);
    }

    public function test_Exception_AssertHeaderFieldValue_Object()
    {
        $this->expectException(InvalidArgumentException::class);

        $message = new Message();
        $mockObject = new stdClass();
        $mockObject->test = 1;

        // Exception:
        // => The header field value only accepts string and array, but "object" provided.
        $newMessage = $message->withHeader('hello-world', $mockObject);
    }

    public function test_Exception_AssertHeaderFieldValue_Array()
    {
        $this->expectException(InvalidArgumentException::class);

        // An invalid type is inside the array.
        $testArr = [
            'test',
            true
        ];

        $message = new Message();

        // Exception:
        // => The header values only accept string and number, but "boolean" provided.
        $newMessage = $message->withHeader('hello-world', $testArr);
    }

    public function test_Exception_AssertHeaderFieldValue_InvalidString()
    {
        $this->expectException(InvalidArgumentException::class);

        $message = new Message();

        // Exception:
        // => "This string contains many invisible spaces." is not valid header 
        //    value, it must contains visible ASCII characters only.
        $newMessage = $message->withHeader('hello-world', 'This string contains many invisible spaces.');

        // $newMessage = $message->withHeader('hello-world', 'This string contains visible space.');
    }
}
