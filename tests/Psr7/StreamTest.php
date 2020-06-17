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
use Psr\Http\Message\StreamInterface;
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

        $this->assertTrue(($stream instanceof StreamInterface));

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

        $resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'c');
        $stream = new Stream($resource);
        $meta = $stream->getMetadata();

        $this->assertTrue($stream->isWritable());
        $this->assertFalse($stream->isReadable());

        $resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r');
        $stream = new Stream($resource);
        $meta = $stream->getMetadata();

        $this->assertFalse($stream->isWritable());
        $this->assertTrue($stream->isReadable());
    }

    public function test__toString()
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('Foo Bar');

        ob_start();
        echo $stream;
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame('Foo Bar', $output);
    }

    public function test_getSize()
    {
        $resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
        $stream = new Stream($resource);
        $this->assertSame($stream->getSize(), 15166);

        $stream->close();
    }

    public function test_getMetadata()
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
        $this->assertEquals($stream->getMetadata('mode'), 'r+');

        $stream->close();

        $this->assertEquals($stream->getMetadata(), null);
    }

    public function test_SeekAndRewind()
    {
        $resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
        $stream = new Stream($resource);

        $stream->seek(10);
        $this->assertSame($stream->tell(), 10);

        $stream->rewind();
        $this->assertSame($stream->tell(), 0);

        $stream->close();
    }


    public function test_ReadAndWrite()
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('Foo Bar');
        $stream->rewind();
        $this->assertSame($stream->read(2), 'Fo');

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

        // Exception: 
        // => Stream should be a resource, but string provided.
        $stream = new Stream('string');
    }

    public function test_Exception_Tell_StreamDoesNotExist()
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream(fopen('php://temp', 'r+'));

        $stream->close();

        // Exception: 
        // => Stream does not exist.
        $position = $stream->tell();
    }

    public function test_Exception_Seek_StreamDoesNotExist()
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream(fopen('php://temp', 'r+'));

        $stream->close();

        // Exception: 
        // => Stream does not exist.
        $stream->seek(10);
    }

    public function test_Exception_Seek_NotSeekable()
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream(fopen('php://temp', 'r'));

        $reflection = new ReflectionObject($stream);
        $seekable = $reflection->getProperty('seekable');
        $seekable->setAccessible(true);
        $seekable->setValue($stream, false);

        // Exception: 
        // => Stream is not seekable.
        $stream->seek(10);
    }

    public function test_Exception_Seek_StreamDoesNotSeekable()
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream(fopen('php://temp', 'r'));

        // Exception: 
        // => Set position equal to offset bytes.. Unable to seek to stream at position 10
        $stream->seek(10);
    }

    public function test_Exception_Write_StreamDoesNotExist()
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream(fopen('php://temp', 'r+'));

        $stream->close();

        // Exception: 
        // => Stream does not exist.
        $stream->write('Foo Bar');
    }

    public function test_Exception_Read_StreamDoesNotExist()
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('Foo Bar');
        $stream->rewind();
        $stream->close();

        // Exception: 
        // => Stream does not exist.
        $stream->read(2);
    }

    public function test_Exception_getContents_StreamDoesNotExist()
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('Foo Bar');
        $stream->rewind();
        $stream->close();

        // Exception: 
        // => Stream does not exist.
        $result = $stream->getContents();
    }

    public function test_Exception_getContents_StreamIsNotReadable()
    {
        $this->expectException(RuntimeException::class);

        $stream = new Stream(fopen('php://temp', 'r+'));
        $stream->write('Foo Bar');
        $stream->rewind();

        $reflection = new ReflectionObject($stream);
        $seekable = $reflection->getProperty('readable');
        $seekable->setAccessible(true);
        $seekable->setValue($stream, false);

        // Exception: 
        // => Unable to read stream contents.
        $result = $stream->getContents();
    }
}