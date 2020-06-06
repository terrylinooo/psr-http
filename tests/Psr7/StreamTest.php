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

        $this->assertEquals($resource, $stream->detach());
    }
}
