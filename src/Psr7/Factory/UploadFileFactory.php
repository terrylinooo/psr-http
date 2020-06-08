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
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Shieldon\Psr7\UploadedFile;

use InvalidArgumentException;

/**
 * Uploaded File Factory
 */
class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int             $size            = null,
        int             $error           = \UPLOAD_ERR_OK,
        string          $clientFilename  = null,
        string          $clientMediaType = null
    ): UploadedFileInterface
    {
        if (! $stream->isReadable()) {
            throw new InvalidArgumentException(
                'File is not readable.'
            );
        }

        return new UploadedFile(
            $stream,
            $clientFilename,
            $clientMediaType,
            $size,
            $error
        );
    }
}