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

namespace Shieldon\Psr7\Factory;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Shieldon\Psr7\Stream;
use InvalidArgumentException;
use RuntimeException;

use function fopen;
use function fwrite;
use function is_resource;
use function preg_match;
use function rewind;

/**
 * PSR-17 Stream Factory
 */
class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $content);
        rewind($resource);

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        if ($mode === '' || ! preg_match('/^[rwaxce]{1}[bt]{0,1}[+]{0,1}+$/', $mode)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid file opening mode "%s"',
                    $mode
                )
            );
        }

        $resource = @fopen($filename, $mode);

        if (! is_resource($resource)) {
            throw new RuntimeException(
                sprintf(
                    'Unable to open file at "%s"',
                    $filename
                )
            );
        }

        return new Stream($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        if (! is_resource($resource)) {
            $resource = fopen('php://temp', 'r+');
        }

        return new Stream($resource);
    }
}
