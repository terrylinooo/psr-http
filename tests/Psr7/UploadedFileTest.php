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
use Psr\Http\Message\UploadedFileInterface;
use Shieldon\Psr7\UploadedFile;
use Shieldon\Psr7\Stream;

use InvalidArgumentException;
use RuntimeException;
use ReflectionObject;

class UploadedFileTest extends TestCase
{
    public function test__construct()
    {
        // Test 1

        $uploadedFile = new UploadedFile(
            '/tmp/php200A.tmp', // source
            'example1.jpg',     // name
            'image/jpeg',       // type
            100000,             // size
            0                   // error
        );

        $this->assertTrue(($uploadedFile instanceof UploadedFileInterface));

        // Test 2

        $resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
        $stream = new Stream($resource);
        $uploadedFile = new UploadedFile($stream);
        $stream2 = $uploadedFile->getStream();

        $this->assertEquals($stream, $stream2);
    }

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */

    public function testExceptionInvalidSource()
    {
        $this->expectException(InvalidArgumentException::class);

        $uploadedFile = new UploadedFile([]);
    }

    public function testExceptionGetStreamTest1()
    {
        $this->expectException(RuntimeException::class);

        // Test 1: Source is not a stream.

        $uploadedFile = new UploadedFile(
            '/tmp/php200A.tmp', // source
            'example1.jpg',     // name
            'image/jpeg',       // type
            100000,             // size
            0                   // error
        );

        $stream = $uploadedFile->getStream();
    }

    public function testExceptionGetStreamTest2()
    {
        $this->expectException(RuntimeException::class);

        // Test 2: Stream has been moved.

        $resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
        $stream = new Stream($resource);
        $uploadedFile = new UploadedFile($stream);

        $targetPath = save_testing_file('shieldon_logo_moved_from_stream.png');
        $uploadedFile->moveTo($targetPath);

        if (! file_exists($targetPath)) {
            // Remind us there is something wrong on this test.
            $this->assertTrue(false);
        }

        unlink($targetPath);

        // Should throw an Exception.
        $stream = $uploadedFile->getStream();
    }
}
