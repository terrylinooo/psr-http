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
        if (! copy($sourceFile, $cloneFile)) {
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
