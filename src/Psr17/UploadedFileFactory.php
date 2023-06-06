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

namespace Shieldon\Psr17;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Shieldon\Psr7\UploadedFile;
use Shieldon\Psr7\Utils\UploadedFileHelper;
use InvalidArgumentException;

/**
 * PSR-17 Uploaded File Factory
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
        if (!$stream->isReadable()) {
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

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Create an array with UriInterface structure.
     *
     * @return array
     */
    public static function fromGlobal(): array
    {
        $filesParams = $_FILES ?? [];
        $uploadedFiles = [];

        if (!empty($filesParams)) {
            $uploadedFiles = UploadedFileHelper::uploadedFileSpecsConvert(
                UploadedFileHelper::uploadedFileParse($filesParams)
            );
        }

        return $uploadedFiles;
    }
}
