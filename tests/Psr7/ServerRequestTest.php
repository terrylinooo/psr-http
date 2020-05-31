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
use Shieldon\Psr7\ServerRequest;
use Shieldon\Psr7\UploadedFile;
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

    public function testProperties()
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
        $this->assertSame($parsedBody, []);
        $this->assertSame($queryParams, []);
        $this->assertSame($uploadedFiles, []);
        $this->assertSame($attributes, []);;
    }

    public function testGetSeriesMethods()
    {
        // Test 1

        $serverRequest = self::getServerRequest();

        $this->assertSame($serverRequest->getServerParams(), []);
        $this->assertSame($serverRequest->getCookieParams(), []);
        $this->assertSame($serverRequest->getParsedBody(), []);
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

    public function testWithSeriesMethods()
    {
        $serverRequest = self::getServerRequest();

        $newUpload = ServerRequest::uploadedFileSpecsConvert(
            ServerRequest::uploadedFileParse(self::mockFile(2))
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

    public function testParseUploadedFiles()
    {
        $files = [
            
            // <input type="file" name="file1">

            'files1' => [
                'name' => 'example1.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php200A.tmp',
                'error' => 0,
                'size' => 100000,
            ],

            // <input type="file" name="files2[a]">
            // <input type="file" name="files2[b]">

            'files2' => [
                'name' => [
                    'a' => 'example21.jpg',
                    'b' => 'example22.jpg',
                ],
                'type' => [
                    'a' => 'image/jpeg',
                    'b' => 'image/jpeg',
                ],
                'tmp_name' => [
                    'a' => '/tmp/php343C.tmp',
                    'b' => '/tmp/php343D.tmp',
                ],
                'error' => [
                    'a' => 0,
                    'b' => 0,
                ],
                'size' => [
                    'a' => 125100,
                    'b' => 145000,
                ],
            ],

            // <input type="file" name="files3[]">
            // <input type="file" name="files3[]">

            'files3' => [
                'name' => [
                    0 => 'example31.jpg',
                    1 => 'example32.jpg',
                ],
                'type' => [
                    0 => 'image/jpeg',
                    1 => 'image/jpeg',
                ],
                'tmp_name' => [
                    0 => '/tmp/php310C.tmp',
                    1 => '/tmp/php313D.tmp',
                ],
                'error' => [
                    0 => 0,
                    1 => 0,
                ],
                'size' => [
                    0 => 200000,
                    1 => 300000,
                ],
            ],

            // <input type="file" name="files4[details][avatar]">
            
            'files4' => [
                'name' => [
                    'details' => [
                        'avatar' => 'my-avatar.png',
                    ],
                ],
                'type' => [
                    'details' => [
                        'avatar' => 'image/png',
                    ],
                ],
                'tmp_name' => [
                    'details' => [
                        'avatar' => '/tmp/phpmFLrzD',
                    ],
                ],
                'error' => [
                    'details' => [
                        'avatar' => 0,
                    ],
                ],
                'size' => [
                    'details' => [
                        'avatar' => 90996,
                    ],
                ],
            ],
        ];

        $expectedFiles = [
            'files1' => [
                'name' => 'example1.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php200A.tmp',
                'error' => 0,
                'size' => 100000,
            ],
            'files2' => [
                'a' => [
                    'tmp_name' => '/tmp/php343C.tmp',
                    'name' => 'example21.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 125100,
                ],
                'b' => [
                    'tmp_name' => '/tmp/php343D.tmp',
                    'name' => 'example22.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 145000,
                ],
            ],
            'files3' => [
                0 => [
                    'tmp_name' => '/tmp/php310C.tmp',
                    'name' => 'example31.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 200000,
                ],
                1 => [
                    'tmp_name' => '/tmp/php313D.tmp',
                    'name' => 'example32.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 300000,
                ],
            ],
            'files4' => [
                'details' => [
                    'avatar' => [
                        'tmp_name' => '/tmp/phpmFLrzD',
                        'name' => 'my-avatar.png',
                        'type' => 'image/png',
                        'error' => 0,
                        'size' => 90996,
                    ],
                ],
            ],
        ];

        $results = ServerRequest::uploadedFileParse($files);

        $this->assertEquals($results, $expectedFiles);
    }

    public function testUploadedFileSpecsConvert()
    {
        $formattedFiles = [
            'files1' => [
                'name' => 'example1.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php200A.tmp',
                'error' => 0,
                'size' => 100000,
            ],
            'files2' => [
                'a' => [
                    'tmp_name' => '/tmp/php343C.tmp',
                    'name' => 'example21.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 125100,
                ],
                'b' => [
                    'tmp_name' => '/tmp/php343D.tmp',
                    'name' => 'example22.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 145000,
                ],
            ],
            'files3' => [
                0 => [
                    'tmp_name' => '/tmp/php310C.tmp',
                    'name' => 'example31.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 200000,
                ],
                1 => [
                    'tmp_name' => '/tmp/php313D.tmp',
                    'name' => 'example32.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 300000,
                ],
            ],
            'files4' => [
                'details' => [
                    'avatar' => [
                        'tmp_name' => '/tmp/phpmFLrzD',
                        'name' => 'my-avatar.png',
                        'type' => 'image/png',
                        'error' => 0,
                        'size' => 90996,
                    ],
                ],
            ],
        ];

        $expectedFiles = [
            'files1' => new UploadedFile(
                '/tmp/php200A.tmp',
                'example1.jpg',
                'image/jpeg',
                100000,
                0
            ),
            'files2' => [
                'a' => new UploadedFile(
                    '/tmp/php343C.tmp',
                    'example21.jpg',
                    'image/jpeg',
                    125100,
                    0
                ),
                'b' => new UploadedFile(
                    '/tmp/php343D.tmp',
                    'example22.jpg',
                    'image/jpeg',
                    145000,
                    0
                )
            ],
            'files3' => [
                0 => new UploadedFile(
                    '/tmp/php310C.tmp',
                    'example31.jpg',
                    'image/jpeg',
                    200000,
                    0
                ),
                1 => new UploadedFile(
                    '/tmp/php313D.tmp',
                    'example32.jpg',
                    'image/jpeg',
                    300000,
                    0
                )
                ],
            'files4' => [
                'details' => [
                    'avatar' => new UploadedFile(
                        '/tmp/phpmFLrzD',
                        'my-avatar.png',
                        'image/png',
                        90996,
                        0
                    )
                ],
            ],
        ];

        $results = ServerRequest::uploadedFileSpecsConvert($formattedFiles);

        $this->assertEquals($results, $expectedFiles);
    }

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */

    public function testAssertUploadedFiles()
    {
        $this->expectException(InvalidArgumentException::class);

        $serverRequest = new ServerRequest('GET', 'https://example.com');

        $reflection = new ReflectionObject($serverRequest);
        $assertUploadedFiles = $reflection->getMethod('assertUploadedFiles');
        $assertUploadedFiles->setAccessible(true);
        $assertUploadedFiles->invokeArgs($serverRequest, [
            [
                ['files' => '']
            ]
        ]);
    }

    function testAsertParsedBody()
    {
        $this->expectException(InvalidArgumentException::class);

        $serverRequest = new ServerRequest('GET', 'https://example.com');

        $reflection = new ReflectionObject($serverRequest);
        $assertParsedBody = $reflection->getMethod('assertParsedBody');
        $assertParsedBody->setAccessible(true);
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
