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
use Psr\Http\Message\UploadedFileInterface;
use InvalidArgumentException;
use RuntimeException;

use function is_string;
use function is_writable;
use function move_uploaded_file;
use function php_sapi_name;
use function rename;

use const UPLOAD_ERR_CANT_WRITE;
use const UPLOAD_ERR_EXTENSION;
use const UPLOAD_ERR_FORM_SIZE;
use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_NO_TMP_DIR;
use const UPLOAD_ERR_OK;
use const UPLOAD_ERR_PARTIAL;

/*
 * Describes a data stream.
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * The full path of the file provided by client.
     *
     * @var string|null
     */
    protected $file;

    /**
     * A stream representing the uploaded file.
     *
     * @var StreamInterface
     */
    protected $stream;

    /**
     * The file size based on the "size" key in the $_FILES array.
     *
     * @var int|null
     */
    protected $size;

    /**
     * The filename based on the "name" key in the $_FILES array.
     *
     * @var string|null
     */
    protected $name;

    /**
     * The type of a file. This value is based on the "type" key in the $_FILES array.
     *
     * @var string|null
     */
    protected $type;

    /**
     * The error code associated with the uploaded file.
     *
     * @var int
     */
    protected $error = 0;

    /**
     * Check if the uploaded file has been moved or not.
     *
     * @var bool
     */
    protected $isMoved = false;

    /**
     * UploadedFile constructor.
     * 
     * @param string|StreamInterface $source The full path of a file or stream.
     * @param string|null            $name   The file name.
     * @param string|null            $type   The file media type.
     * @param int|null               $size   The file size in bytes.
     * @param int                    $error  The status code of the upload.
     */
    public function __construct(
                $source       ,
        ?string $name   = null,
        ?string $type   = null,
        ?int    $size   = null,
        int     $error  = 0
    ) {

        if (is_string($source)) {
            $this->file = $source;

        } elseif ($source instanceof StreamInterface) {
            $this->stream = $source;

        } else {
            throw new InvalidArgumentException(
                'First argument accepts only a string or a stream instance.'
            );
        }

        $this->name  = $name;
        $this->type  = $type;
        $this->size  = $size;
        $this->error = $error;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream(): StreamInterface
    {
        if ($this->isMoved) {
            throw new RuntimeExcpetion(
                'The uploaded file has been moved,'
            );
        }

        if (! $this->stream) {
            throw new RuntimeExcpetion(
                'No stream is available or can be created.'
            );
        }

        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo($targetPath): void
    {
        if ($this->isMoved) {
            throw new RuntimeException('Uploaded file already moved');
        }

        if ($this->isFile()) {

            if (! is_writable(dirname($targetPath))) {
                throw new InvalidArgumentException(
                    sprintf(
                        'The target path "%s" is not writable.',
                        $targetPath
                    )
                );
            }

            if (php_sapi_name() === 'cli') {

                if (! rename($this->file, $targetPath)) {
                    throw new RuntimeException(
                        sprintf(
                            'Could not rename the file to the target path "%s".',
                            $targetPath
                        )
                    );
                }
            } else {

                if (! is_uploaded_file($this->file)) {
                    throw new RuntimeException(
                        sprintf(
                            '"%s" is invalid uploaded file.',
                            $this->file
                        )
                    );
                }

                if (! move_uploaded_file($this->file, $targetPath)) {
                    throw new RuntimeException(
                        sprintf(
                            'Could not move the file to the target path "%s".',
                            $targetPath
                        )
                    );
                }
            }
        }

        if ($this->isStream()) {

            // Todo
        }

        $this->isMoved = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->type;
    }

    /*
    |--------------------------------------------------------------------------
    | Non-PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Get error message when uploading files.
     *
     * @return string
     */
    protected function getErrorMessage(): string
    {
        switch ($this->error) {

            case UPLOAD_ERR_OK:
                $message = 'There is no error, the file uploaded with success.';
                break;

            case UPLOAD_ERR_INI_SIZE:
                $message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                break;
    
            case UPLOAD_ERR_FORM_SIZE:
                $message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
                break;

            case UPLOAD_ERR_PARTIAL:
                $message = 'The uploaded file was only partially uploaded.';
                break;

            case UPLOAD_ERR_NO_FILE:
                $message = 'No file was uploaded.';
                break;

            case UPLOAD_ERR_NO_TMP_DIR:
                $message = 'Missing a temporary folder.';
                break;

            case UPLOAD_ERR_CANT_WRITE:
                $message = 'Failed to write file to disk.';
                break;

            case UPLOAD_ERR_EXTENSION:
                $message = 'File upload stopped by extension.';
                break;

            default:
                $message = 'Unknown upload error.';
                break;
        }

        return $message;
    }

    /**
     * Is stream?
     *
     * @return bool
     */
    protected function isStream(): bool
    {
        return ($this->stream instanceof StreamInterface);
    }

    /**
     * Is file?
     *
     * @return bool
     */
    protected function isFile(): bool
    {
        return (is_string($this->file) && ! empty($this->file));
    }
}
