<?php 
/**
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
use function preg_match;
use function sprintf;
use function stream_get_contents;
use function stream_get_meta_data;

use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;
use const PREG_OFFSET_CAPTURE;

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
     * The size of the stream.
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

        $meta = $this->getMetadata();

        $this->readable = false;
        $this->writable = false;

        // The mode parameter specifies the type of access you require to 
        // the stream. @see https://www.php.net/manual/en/function.fopen.php
        if (strpos($meta['mode'], '+') !== false) {
            $this->readable = true;
            $this->writable = true;
        }

        if (preg_match('/^[waxc][t|b]{0,1}$/', $meta['mode'], $matches, PREG_OFFSET_CAPTURE)) {
            $this->writable = true;
        }

        if (strpos($meta['mode'], 'r') !== false) {
            $this->readable = true;
        }

        $this->seekable = $meta['seekable'];
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
        if ($this->isStream()) {
            fclose($this->stream);
        }

        $this->detach();
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        if (!$this->isStream()) {
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
    public function getSize(): ?int
    {
        if (!$this->isStream()) {
            return null;
        }

        if ($this->size === null) {
            $stats = fstat($this->stream);
            $this->size = $stats['size'] ?? null;
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        $this->assertPropertyStream();

        $pointer = false;

        if ($this->stream) {
            $pointer = ftell($this->stream);
        }

        if ($pointer === false) {

            // @codeCoverageIgnoreStart

            throw new RuntimeException(
                'Unable to get the position of the file pointer in stream.'
            );

            // @codeCoverageIgnoreEnd
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
        $this->assertPropertyStream();

        if (!$this->seekable) {
            throw new RuntimeException(
                'Stream is not seekable.'
            );
        }

        $offset = (int) $offset;
        $whence = (int) $whence;

        $message = [
            SEEK_CUR => 'Set position to current location plus offset.',
            SEEK_END => 'Set position to end-of-stream plus offset.',
            SEEK_SET => 'Set position equal to offset bytes.',
        ];

        $errorMsg = $message[$whence] ?? 'Unknown error.';

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException(
                sprintf(
                    '%s. Unable to seek to stream at position %s',
                    $errorMsg,
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
        $this->assertPropertyStream();

        $size = 0;

        if ($this->isWritable()) {
            $size = fwrite($this->stream, $string);
        }

        if ($size === false) {

            // @codeCoverageIgnoreStart

            throw new RuntimeException(
                'Unable to write to stream.'
            );

            // @codeCoverageIgnoreEnd
        }

        // Make sure that `getSize()`will count the correct size again after writing anything.
        $this->size = null;

        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $this->assertPropertyStream();

        $string = false;

        if ($this->isReadable()) {
            $string = fread($this->stream, $length);
        }

        if ($string === false) {

            // @codeCoverageIgnoreStart

            throw new RuntimeException(
                'Unable to read from stream.'
            );

            // @codeCoverageIgnoreEnd
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        $this->assertPropertyStream();

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
        if ($this->isStream()) {
            $this->meta = stream_get_meta_data($this->stream);
            
            if (!$key) {
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

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

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
        if (!is_resource($stream)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Stream should be a resource, but "%s" provided.',
                    gettype($stream)
                )
            );
        }
    }

    /**
     * Throw an exception if the property does not exist.
     *
     * @return RuntimeException
     */
    protected function assertPropertyStream(): void
    {
        if (!$this->isStream()) {
            throw new RuntimeException(
                'Stream does not exist.'
            );
        }
    }

    /**
     * Check if stream exists or not.
     *
     * @return bool
     */
    protected function isStream(): bool
    {
        return (isset($this->stream) && is_resource($this->stream));
    }
}
