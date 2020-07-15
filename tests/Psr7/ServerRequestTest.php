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
use Shieldon\Psr7\ServerRequest;
use Shieldon\Psr7\UploadedFile;
use Shieldon\Psr7\Utils\UploadedFileHelper;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\MessageInterface;
use ReflectionObject;

class ServerRequestTest extends TestCase
{
    public function test__construct()
    {
        $serverRequest = new ServerRequest('GET', '', '', [], '1.1', [], [], [], [], self::mockFile(1));

        $this->assertTrue(($serverRequest instanceof RequestInterface));
        $this->assertTrue(($serverRequest instanceof MessageInterface));
        $this->assertTrue(($serverRequest instanceof ServerRequestInterface));
    }

    public function test_Properties()
    {
        $serverRequest = self::getServerRequest();

        $properties = [
            'serverParams', 
            'cookieParams', 
            'parsedBody',
            'queryParams',
            'uploadedFiles',
            'attributes',
        ];

        $reflection = new ReflectionObject($serverRequest);

        foreach($properties as $v) {
            $tmp = $reflection->getProperty($v);
            $tmp->setAccessible(true);
            ${$v} = $tmp->getValue($serverRequest);
            unset($tmp);
        }

        $this->assertSame($serverParams, []);
        $this->assertSame($cookieParams, []);
        $this->assertSame($parsedBody, null);
        $this->assertSame($queryParams, []);
        $this->assertSame($uploadedFiles, []);
        $this->assertSame($attributes, []);;
    }

    public function test_GetPrefixMethods()
    {
        // Test 1

        $serverRequest = self::getServerRequest();

        $this->assertSame($serverRequest->getServerParams(), []);
        $this->assertSame($serverRequest->getCookieParams(), []);
        $this->assertSame($serverRequest->getParsedBody(), null);
        $this->assertSame($serverRequest->getQueryParams(), []);
        $this->assertSame($serverRequest->getUploadedFiles(), []);
        $this->assertSame($serverRequest->getAttributes(), []);;

        // Test 2
        $serverRequest = self::getServerRequest(
            'POST', 
            ['foo' => 'bar'],
            ['foo' => 'bar'],
            ['foo' => 'bar'],
            ['foo' => 'bar'],
            self::mockFile(1)
        );

        $this->assertEquals($serverRequest->getServerParams(), ['foo' => 'bar']);
        $this->assertEquals($serverRequest->getCookieParams(), ['foo' => 'bar']);
        $this->assertEquals($serverRequest->getParsedBody(), ['foo' => 'bar']);
        $this->assertEquals($serverRequest->getQueryParams(), ['foo' => 'bar']);
        $this->assertEquals($serverRequest->getAttributes(), []);

        $this->assertEquals($serverRequest->getUploadedFiles(), [
            'files1' => new UploadedFile(
                '/tmp/php200A.tmp',
                'example1.jpg',
                'image/jpeg',
                100000,
                0
            )
        ]);
    }

    public function test_WithPrefixMethods()
    {
        $serverRequest = self::getServerRequest();

        $newUpload = UploadedFileHelper::uploadedFileSpecsConvert(
            UploadedFileHelper::uploadedFileParse(self::mockFile(2))
        );

        $new = $serverRequest->withCookieParams(['foo3' => 'bar3'])
            ->withParsedBody(['foo4' => 'bar4', 'foo5' => 'bar5'])
            ->withQueryParams(['foo6' => 'bar6', 'foo7' => 'bar7'])
            ->withAttribute('foo8', 'bar9')
            ->withUploadedFiles($newUpload);

        $this->assertEquals($new->getServerParams(), []);
        $this->assertEquals($new->getCookieParams(), ['foo3' => 'bar3']);
        $this->assertEquals($new->getParsedBody(), ['foo4' => 'bar4', 'foo5' => 'bar5']);
        $this->assertEquals($new->getQueryParams(), ['foo6' => 'bar6', 'foo7' => 'bar7']);
        $this->assertEquals($new->getAttribute('foo8'), 'bar9');

        $this->assertEquals($new->getUploadedFiles(), [
            'avatar' => new UploadedFile(
                '/tmp/phpmFLrzD',
                'my-avatar.png',
                'image/png',
                90996,
                0
            )
        ]);

        $new2 = $new->withoutAttribute('foo8');

        $this->assertEquals($new2->getAttribute('foo8'), null);
    }

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */

    public function test_Exception_AssertUploadedFiles()
    {
        $this->expectException(InvalidArgumentException::class);

        $serverRequest = new ServerRequest('GET', 'https://example.com');

        $reflection = new ReflectionObject($serverRequest);
        $assertUploadedFiles = $reflection->getMethod('assertUploadedFiles');
        $assertUploadedFiles->setAccessible(true);

        // Exception:
        // => Invalid PSR-7 array structure for handling UploadedFile.
        $assertUploadedFiles->invokeArgs($serverRequest, [
            [
                ['files' => '']
            ]
        ]);
    }

    public function test_Exception_AsertParsedBody()
    {
        $this->expectException(InvalidArgumentException::class);

        $serverRequest = new ServerRequest('GET', 'https://example.com');

        $reflection = new ReflectionObject($serverRequest);
        $assertParsedBody = $reflection->getMethod('assertParsedBody');
        $assertParsedBody->setAccessible(true);

        // Exception:
        // => Only accepts array, object and null, but string provided.
        $assertParsedBody->invokeArgs($serverRequest, ['invalid string body']);

        // Just for code coverage.
        $assertParsedBody->invokeArgs($serverRequest, [[]]);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods that help for testing.
    |--------------------------------------------------------------------------
    */

    /**
     * Get a ServerRequest instance for testing simply.
     *
     * @param string $method
     * @param array  $server
     * @param array  $cookie
     * @param array  $post
     * @param array  $get
     * @param array  $files
     * 
     * @return ServerRequest
     */
    private static function getServerRequest(
        $method = 'GET',
        $server = []   ,
        $cookie = []   ,
        $post   = []   ,
        $get    = []   ,
        $files  = []
    ) {
        return new ServerRequest(
            $method,
            '',
            '',
            [],
            '1.1',
            $server,
            $cookie,
            $post,
            $get,
            $files
        );
    }

    /**
     * Moke a $_FILES variable for testing simply.
     *
     * @return array
     */
    private static function mockFile($item = 1)
    {
        if ($item === 1) {
            $_FILES['files1'] = [
                'name' => 'example1.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php200A.tmp',
                'error' => 0,
                'size' => 100000,
            ];
        }

        if ($item === 2) {
            $_FILES['avatar'] = [
                'tmp_name' => '/tmp/phpmFLrzD',
                'name' => 'my-avatar.png',
                'type' => 'image/png',
                'error' => 0,
                'size' => 90996,
            ];
        }

        return $_FILES;
    }
}
