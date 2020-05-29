<?php 
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Shieldon\Psr7;

use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;
use RuntimeException;

use function fclose;
use function fread;
use function fseek;
use function fstat;
use function ftell;
use function fwrite;
use function gettype;
use function is_resource;
use function sprintf;
use function stream_get_contents;
use function stream_get_meta_data;

use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;

/*
 * Describes a data stream.
 */
class Stream implements StreamInterface
{
    /**
     * @var bool
     */
    protected $readable;

    /**
     * @var bool
     */
    protected $writable;

    /**
     * @var bool
     */
    protected $seekable;

    /**
     * Undocumented variable
     *
     * @var int|null
     */
    protected $size;

    /**
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @var array
     */
    protected $meta;

    /**
     * Typically a PHP resource.
     *
     * @var resource
     */
    protected $stream;

    /**
     * Stream constructor
     * 
     * @param resource $stream A valid resource.
     */
    public function __construct($stream)
    {
        $this->assertStream($stream);
        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if (isset($this->stream)) {
            $this->detach();
        }

        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        if (! isset($this->stream) || ! $this->stream) {
            return null;
        }

        $legacy = $this->stream;
        
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;
        $this->size = null;
        $this->meta = [];

        unset($this->stream);

        return $legacy;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if (! isset($this->stream) || ! $this->stream) {
            return null;
        }

        if (! $this->size) {
            $stats = fstat($this->stream);

            if ($stats) {
                $this->size = isset($stats['size']) ? $stats['size'] : null;
            }
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        if (! isset($this->stream) || ! $this->stream) {
            throw new RuntimeException(
                'Stream does not exist.'
            );
        }

        $pointer = false;

        if ($this->stream) {
            $pointer = ftell($this->stream);
        }

        if ($pointer === false) {
            throw new RuntimeException(
                'Unable to get the position of the file pointer in stream.'
            );
        }

        return $pointer;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return $this->stream ? feof($this->stream) : true;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (! isset($this->stream) || ! $this->stream) {
            throw new RuntimeException(
                'Stream does not exist.'
            );
        }

        if (! $this->seekable()) {
            throw new RuntimeException(
                'Stream is not seekable.'
            );
        }

        $offset = (int) $offset;
        $whence = (int) $whence;

        switch ($whence) {

            case SEEK_SET:
                $message = 'Set position equal to offset bytes.';
                break;

            case SEEK_CUR:
                $message = 'Set position to current location plus offset.';
                break;
    
            case SEEK_END:
                $message = 'Set position to end-of-stream plus offset.';
                break;

            default:
                $message = 'Unknown error.';
                break;
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException(
                sprintf(
                    '%s. Unable to seek to stream at position %s',
                    $message,
                    $offset
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        if (! isset($this->stream) || ! $this->stream) {
            throw new RuntimeException(
                'Stream does not exist.'
            );
        }

        $size = 0;

        if ($this->isWritable()) {
            $size = fwrite($this->stream, $string);
        }

        if ($size === false) {
            throw new RuntimeException(
                'Unable to write to stream.'
            );
        }

        // Make sure that `getSize()`will count the correct size again after writting anything.
        $this->size = null;

        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        if (! isset($this->stream) || ! $this->stream) {
            throw new RuntimeException(
                'Stream does not exist.'
            );
        }

        $string = false;

        if ($this->isReadable()) {
            $string = fread($this->stream, $length);
        }

        if ($string === false) {
            throw new RuntimeException(
                'Unable to read from stream.'
            );
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if (! isset($this->stream) || ! $this->stream) {
            throw new RuntimeException(
                'Stream does not exist.'
            );
        }

        $string = false;

        if ($this->isReadable()) {
            $string = stream_get_contents($this->stream);
        }

        if ($string === false) {
            throw new RuntimeException(
                'Unable to read stream contents.'
            );
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if ($this->stream) {
            $this->meta = stream_get_meta_data($this->stream);
            
            if (! $key) {
                return $this->meta;
            }

            if (isset($this->meta[$key])) {
                return $this->meta[$key];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        if ($this->isSeekable()) {
            $this->rewind();
        }

        return $this->getContents();
    }


    /**
     * Throw exception if stream is not a valid PHP resource.
     *
     * @param resource $stream A valid resource.
     * 
     * @return void
     * 
     * InvalidArgumentException
     */
    protected function assertStream($stream): void
    {
        if (! is_resource($stream)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Stream should be a resource, but %s provided.',
                    gettype(stream)
                )
            );
        }
    }
}
