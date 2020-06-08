<?php 
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Psr7\Factory;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use Shieldon\Psr7\Factory\UploadedFileFactory;
use Shieldon\Psr7\Factory\StreamFactory;

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
}
