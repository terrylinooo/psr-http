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
use Psr\Http\Message\UploadedFileInterface;
use Shieldon\Psr7\UploadedFile;
use Shieldon\Psr17\UploadedFileFactory;
use Shieldon\Psr17\StreamFactory;
use ReflectionObject;
use InvalidArgumentException;

class UploadFileFactoryTest extends TestCase
{
    public function test_createUploadedFile()
    {
        $uploadedFileFactory = new UploadedFileFactory();

        $sourceFile = BOOTSTRAP_DIR . '/sample/shieldon_logo.png';
        $cloneFile = save_testing_file('shieldon_logo_clone_2.png');
        $targetPath = save_testing_file('shieldon_logo_moved_from_file_2.png');

        // Clone a sample file for testing MoveTo method.
        if (!copy($sourceFile, $cloneFile)) {
            $this->assertTrue(false);
        }

        $streamFactory = new StreamFactory();
        $stream =  $streamFactory->createStreamFromFile($cloneFile);

        $uploadedFileFactory = new UploadedFileFactory();

        $uploadedFile = $uploadedFileFactory->createUploadedFile($stream);
        $this->assertTrue(($uploadedFile instanceof UploadedFileInterface));

        $uploadedFile->moveTo($targetPath);

        if (file_exists($targetPath)) {
            $this->assertTrue(true);
        }

        unlink($targetPath);
    }

    public function test_createFromGlobal()
    {
        $_FILES = [
            
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

        $results = UploadedFileFactory::fromGlobal();

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

        $this->assertEquals($results, $expectedFiles);
    }

    public function testExample()
    {
        $_FILES = [
            'foo' => [
                'name' => 'example1.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php200A.tmp',
                'error' => 0,
                'size' => 100000,
            ]
        ];
        
        $uploadFileArr = UploadedFileFactory::fromGlobal();
        
        $filename = $uploadFileArr['foo']->getClientFilename();

        $this->assertEquals('example1.jpg', $filename);
    }

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */

    public function test_Exception_FileIsNotReadable()
    {
        $this->expectException(InvalidArgumentException::class);

        $uploadedFileFactory = new UploadedFileFactory();

        $sourceFile = BOOTSTRAP_DIR . '/sample/shieldon_logo.png';

        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStreamFromFile($sourceFile);

        $reflection = new ReflectionObject($stream);
        $readable = $reflection->getProperty('readable');
        $readable->setAccessible(true);
        $readable->setValue($stream, false);

        $uploadedFileFactory = new UploadedFileFactory();

        // Exception: 
        // => File is not readable.
        $uploadedFile = $uploadedFileFactory->createUploadedFile($stream);
    }
}
