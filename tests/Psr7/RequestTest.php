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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\UriInterface;
use Shieldon\Psr7\Request;
use Shieldon\Psr7\Message;
use InvalidArgumentException;
use RuntimeException;
use ReflectionObject;

class RequestTest extends TestCase
{
    public function test__construct()
    {
        $request = new Request('GET', '', '', [], '1.1');

        $this->assertTrue(($request instanceof RequestInterface));
        $this->assertTrue(($request instanceof MessageInterface));
    }

    public function test_GetPrefixMethods()
    {
        $request = new Request('POST', 'https://terryl.in/zh/?test=test', '', ['test' => 1234], '1.1');

        $this->assertSame($request->getRequestTarget(), '/zh/?test=test');
        $this->assertSame($request->getMethod(), 'POST');

        $uri = $request->getUri();

        $this->assertTrue(($uri instanceof UriInterface));

        // Let's double check the Uri instance again.

        $this->assertSame($uri->getScheme(), 'https');
        $this->assertSame($uri->getHost(), 'terryl.in');
        $this->assertSame($uri->getUserInfo(), '');
        $this->assertSame($uri->getPath(), '/zh/');
        $this->assertSame($uri->getPort(), null);
        $this->assertSame($uri->getQuery(), 'test=test');
        $this->assertSame($uri->getFragment(), ''); 
    }

    public function test_WithPrefixMethods()
    {
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

        $request = new Request('GET', 'https://terryl.in/zh/', '', [], '1.1');

        $request->withMethod('POST');
        $request->withUri(new Uri('https://play.google.com'));
  
        



    }
}
