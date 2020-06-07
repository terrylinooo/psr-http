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

class StreamTest extends TestCase
{
    public function test__construct()
    {
        $resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
        $stream = new Stream($resource);

        $expectedMeta = [
            'timed_out' => false,
            'blocked' => true,
            'eof' => false,
            'wrapper_type' => 'plainfile',
            'stream_type' => 'STDIO',
            'mode' => 'r+',
            'unread_bytes' => 0,
            'seekable' => true,
            'uri' => '/home/terrylin/data/psr7/tests/sample/shieldon_logo.png',
        ];

        $meta = $stream->getMetadata();

        $this->assertEquals($expectedMeta['mode'], $meta['mode']);

        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isSeekable());

        $this->assertTrue(
            is_integer($stream->getSize())
        );

        $this->assertTrue(
            is_bool($stream->eof())
        );

        $this->assertTrue(
            is_integer($stream->tell())
        );

        // close.
        $this->assertEquals($resource, $stream->detach());

        $this->assertTrue(
            is_null($stream->getSize())
        );

        $this->assertTrue(
            is_null($stream->detach())
        );
    }

    public function test__getSize()
    {
        $resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
        $stream = new Stream($resource);
        $this->assertSame($stream->getSize(), 15166);

        $stream->close();
    }

    public function test__seekAndRewind()
    {
        $resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
        $stream = new Stream($resource);

        $stream->seek(10);
        $this->assertSame($stream->tell(), 10);

        $stream->rewind();
        $this->assertSame($stream->tell(), 0);

        $stream->close();
    }

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */

    public function test_Exception_assertStream()
    {
        $this->expectException(InvalidArgumentException::class);

        $stream = new Stream('string');
    }
}
