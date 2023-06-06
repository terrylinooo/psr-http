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
use Psr\Http\Message\UploadedFileInterface;
use Shieldon\Psr7\Stream;
use InvalidArgumentException;
use RuntimeException;

use function file_exists;
use function file_put_contents;
use function is_string;
use function is_uploaded_file;
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
use const LOCK_EX;

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
     * @var StreamInterface|null
     */
    protected $stream;

    /**
     * Is file copy to stream when first time calling getStream().
     *
     * @var bool
     */
    protected $isFileToStream = false;

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
    protected $error;

    /**
     * Check if the uploaded file has been moved or not.
     *
     * @var bool
     */
    protected $isMoved = false;

    /**
     * The type of interface between web server and PHP.
     * This value is typically from `php_sapi_name`, might be changed ony for
     * unit testing purpose.
     *
     * @var string
     */
    private $sapi;

    /**
     * UploadedFile constructor.
     * 
     * @param string|StreamInterface $source The full path of a file or stream.
     * @param string|null            $name   The file name.
     * @param string|null            $type   The file media type.
     * @param int|null               $size   The file size in bytes.
     * @param int                    $error  The status code of the upload.
     * @param string|null            $sapi   Only assign for unit testing purpose.
     */
    public function __construct(
                $source       ,
        ?string $name   = null,
        ?string $type   = null,
        ?int    $size   = null,
        int     $error  = 0   ,
        ?string $sapi   = null
    ) {

        if (is_string($source)) {
            $this->file = $source;

        } elseif ($source instanceof StreamInterface) {
            $this->stream = $source;

        } else {
            throw new InvalidArgumentException(
                'First argument accepts only a string or StreamInterface instance.'
            );
        }

        $this->name  = $name;
        $this->type  = $type;
        $this->size  = $size;
        $this->error = $error;
        $this->sapi  = php_sapi_name();

        if ($sapi) {
            $this->sapi = $sapi;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStream(): StreamInterface
    {
        if ($this->isMoved) {
            throw new RuntimeException(
                'The stream has been moved.'
            );
        }

        if (!$this->isFileToStream && !$this->stream) {
            $resource = @fopen($this->file, 'r');
            if (is_resource($resource)) {
                $this->stream = new Stream($resource);
            }
            $this->isFileToStream = true;
        }

        if (!$this->stream) {
            throw new RuntimeException(
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
            // Throw exception on the second or subsequent call to the method.
            throw new RuntimeException(
                'Uploaded file already moved'
            );
        }

        if (!is_writable(dirname($targetPath))) {
            // Throw exception if the $targetPath specified is invalid.
            throw new RuntimeException(
                sprintf(
                    'The target path "%s" is not writable.',
                    $targetPath
                )
            );
        }

        // Is a file..
        if (is_string($this->file) && ! empty($this->file)) {

            if ($this->sapi === 'cli') {

                if (!rename($this->file, $targetPath)) {

                    // @codeCoverageIgnoreStart

                    // Throw exception on any error during the move operation.
                    throw new RuntimeException(
                        sprintf(
                            'Could not rename the file to the target path "%s".',
                            $targetPath
                        )
                    );

                    // @codeCoverageIgnoreEnd
                }
            } else {

                if (
                    ! is_uploaded_file($this->file) || 
                    ! move_uploaded_file($this->file, $targetPath)
                ) {
                    // Throw exception on any error during the move operation.
                    throw new RuntimeException(
                        sprintf(
                            'Could not move the file to the target path "%s".',
                            $targetPath
                        )
                    );
                }
            }

        } elseif ($this->stream instanceof StreamInterface) {
            $content = $this->stream->getContents();

            file_put_contents($targetPath, $content, LOCK_EX);

            // @codeCoverageIgnoreStart

            if (!file_exists($targetPath)) {
                // Throw exception on any error during the move operation.
                throw new RuntimeException(
                    sprintf(
                        'Could not move the stream to the target path "%s".',
                        $targetPath
                    )
                );
            }

            // @codeCoverageIgnoreEnd

            unset($content, $this->stream);
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
    public function getErrorMessage(): string
    {
        $message = [
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension.',
            UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success.',
        ];

        return $message[$this->error] ?? 'Unknown upload error.';
    }
}
