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
        $stream2 = $uploadedFile->getStream(); // Test `getStream()`

        $this->assertEquals($stream, $stream2);
        $this->assertEquals($stream, $stream2);
    }

    public function test_MoveTo_Sapi_Cli()
    {
        $sourceFile = BOOTSTRAP_DIR . '/sample/shieldon_logo.png';
        $cloneFile = save_testing_file('shieldon_logo_clone.png');
        $targetPath = save_testing_file('shieldon_logo_moved_from_file.png');

        // Clone a sample file for testing MoveTo method.
        if (! copy($sourceFile, $cloneFile)) {
            $this->assertTrue(false);
        }

        $uploadedFile = new UploadedFile(
            $cloneFile,
            'shieldon_logo.png',
            'image/png',
            100000,
            0
        );

        $uploadedFile->moveTo($targetPath);

        if (file_exists($targetPath)) {
            $this->assertTrue(true);
        }

        unlink($targetPath);
    }

    public function test_MoveTo_Mock_Sapi_Fpm()
    {
        $sourceFile = BOOTSTRAP_DIR . '/sample/shieldon_logo.png';
        $cloneFile = save_testing_file('shieldon_logo_clone.png');
        $targetPath = save_testing_file('shieldon_logo_moved_from_file.png');

        // Clone a sample file for testing MoveTo method.
        if (! copy($sourceFile, $cloneFile)) {
            $this->assertTrue(false);
        }

        $uploadedFile = new UploadedFile(
            $cloneFile,
            'shieldon_logo.png',
            'image/png',
            100000,
            0,
            'mock:is_uploaded_file:true'
        );

        $uploadedFile->moveTo($targetPath);

        if (file_exists($targetPath)) {
            $this->assertTrue(true);
        }

        unlink($targetPath);
    }

    public function test_GetPrefixMethods()
    {
        $sourceFile = BOOTSTRAP_DIR . '/sample/shieldon_logo.png';
        $cloneFile = save_testing_file('shieldon_logo_clone.png');

        // Clone a sample file for testing MoveTo method.
        if (! copy($sourceFile, $cloneFile)) {
            $this->assertTrue(false);
        }

        $uploadedFile = new UploadedFile(
            $cloneFile,
            'shieldon_logo.png',
            'image/png',
            100000,
            0
        );

        $this->assertSame($uploadedFile->getSize(), 100000);
        $this->assertSame($uploadedFile->getError(), 0);
        $this->assertSame($uploadedFile->getClientFilename(), 'shieldon_logo.png');
        $this->assertSame($uploadedFile->getClientMediaType(), 'image/png');
        $this->assertSame($uploadedFile->getErrorMessage(), 'There is no error, the file uploaded with success.');
    }

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */

    public function test_Exception_ArgumentIsInvalidSource()
    {
        $this->expectException(InvalidArgumentException::class);

        // Exception:
        // => First argument accepts only a string or StreamInterface instance.
        $uploadedFile = new UploadedFile([]);
    }

    public function test_Exception_GetStream_StreamIsNotAvailable()
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

        // Exception:
        // => No stream is available or can be created.
        $stream = $uploadedFile->getStream();
    }

    public function test_Exception_GetStream_StreamIsMoved()
    {
        $this->expectException(RuntimeException::class);

        // Test 2: Stream has been moved, so can't find it using getStream().

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

        // Exception:
        // => The stream has been moved
        $stream = $uploadedFile->getStream();
    }

    public function test_Exception_MoveTo_FileIsMoved()
    {
        $this->expectException(RuntimeException::class);

        $uploadedFile = new UploadedFile(
            '/tmp/php200A.tmp',
            'shieldon_logo.png',
            'image/png',
            100000,
            0
        );

        $reflection = new ReflectionObject($uploadedFile);
        $isMoved = $reflection->getProperty('isMoved');
        $isMoved->setAccessible(true);
        $isMoved->setValue($uploadedFile, true);

        $targetPath = save_testing_file('shieldon_logo_moved_from_stream.png');

        // Exception: 
        // => The uploaded file has been moved.
        $uploadedFile->moveTo($targetPath);
    }

    public function test_Exception_MoveTo_TargetIsNotWritable()
    {
        $this->expectException(RuntimeException::class);

        $uploadedFile = new UploadedFile(
            BOOTSTRAP_DIR . '/sample/shieldon_logo.png',
            'shieldon_logo.png',
            'image/png',
            100000,
            0
        );

        $uploadedFile->moveTo(BOOTSTRAP_DIR . '/tmp/folder-not-exists/test.png');
    }

    public function test_Exception_MoveTo_FileCannotRename()
    {
        $this->expectException(RuntimeException::class);

        $uploadedFile = new UploadedFile(
            BOOTSTRAP_DIR . '/sample/shieldon_logo.png',
            'shieldon_logo.png',
            'image/png',
            100000,
            0,
            'mock-cannot-rename'
        );

        $targetPath = save_testing_file('shieldon_logo_moved_from_stream.png');

        $uploadedFile->moveTo($targetPath);
    }
}
