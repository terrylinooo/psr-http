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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Shieldon\Psr7\UploadedFile;
use Shieldon\Psr7\Request;
use InvalidArgumentException;

use function array_merge_recursive;
use function rtrim;
use function is_array;
use function is_string;
use function is_numeric;
use function is_null;
use function is_object;
use function sprintf;
use function gettype;

/*
 * Representation of an incoming, server-side HTTP request.
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * Typically derived from PHP's $_SERVER superglobal.
     * 
     * @var array
     */
    protected $serverParams;

    /**
     * Typically derived from PHP's $_COOKIE superglobal.
     * 
     * @var array
     */
    protected $cookieParams;

    /**
     * Typically derived from PHP's $_POST superglobal.
     * 
     * @var array|object|null
     */
    protected $parsedBody;

    /**
     * Typically derived from PHP's $_GET superglobal.
     * 
     * @var array
     */
    protected $queryParams;

    /**
     * Typically derived from PHP's $_FILES superglobal.
     * A collection of uploadFileInterface instances.
     * 
     * @var array
     */
    protected $uploadedFiles;

    /**
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * ServerRequest constructor.
     *
     * @param string                 $method       Request HTTP method
     * @param UriInterface|string    $uri          Request URI object URI or URL
     * @param StreamInterface|string $body         Request body
     * @param array                  $headers      Request headers
     * @param string                 $version      Request protocol version
     * @param array                  $serverParams Typically $_SERVER superglobal
     * @param array                  $cookieParams Typically $_COOKIE superglobal
     * @param array                  $postParams   Typically $_POST superglobal
     * @param array                  $getParams    Typically $_GET superglobal
     * @param array                  $filesParams  Typically $_FILES superglobal
     */
    public function __construct(
        string $method       = 'GET',
               $uri          = ''   ,
               $body         = ''   ,
        array  $headers      = []   ,
        string $version      = '1.1',
        array  $serverParams = []   ,
        array  $cookieParams = []   ,
        array  $postParams   = []   ,
        array  $getParams    = []   ,
        array  $filesParams  = []
    ) {
        parent::__construct($method, $uri, $body, $headers, $version);

        $this->serverParams = $serverParams;
        $this->cookieParams = $cookieParams;
        $this->parsedBody   = $postParams;
        $this->queryParams  = $getParams;

        if (! empty($filesParams)) {
            $this->uploadedFiles = self::uploadedFileSpecsConvert(
                self::uploadedFileParse($filesParams)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->assertUploadedFiles($uploadedFiles);

        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data)
    {
        $this->assertParsedBody($data);

        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name) 
    {
        $clone = clone $this;

        if (isset($this->attributes[$name])) {
            unset($clone->attributes[$name]);
        }

        return $clone;
    }

    /*
    |--------------------------------------------------------------------------
    | Non-PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Create an array for PSR-7 Uploaded File needed.
     * 
     * @param array $files     An array generally from $_FILES
     * @param bool  $isConvert To covert and return $files as an UploadedFile instance.
     * 
     * @return array|UploadedFile
     */
    public static function uploadedFileParse(array $files)
    {
        $specTree = [];

        $specFields = [
            0 => 'tmp_name',
            1 => 'name',
            2 => 'type',
            3 => 'size',
            4 => 'error',
        ];

        foreach ($files as $fileKey => $fileValue) {
            if (is_string($fileValue['tmp_name']) || is_numeric($fileValue['tmp_name'])) {
                $specTree[$fileKey] = $fileValue;

            } elseif (is_array($fileValue['tmp_name'])) {

                // We want to find out how many levels of array it has.
                foreach ($specFields as $i => $attr) {
                    $tmp[$i] = self::uploadedFileNestedFields($fileValue, $attr);
                }

                $parsedTree = array_merge_recursive(
                    $tmp[0], // tmp_name
                    $tmp[1], // name
                    $tmp[2], // type
                    $tmp[3], // size
                    $tmp[4]  // error
                );
  
                $specTree[$fileKey] = $parsedTree;
                unset($tmp, $parsedTree);
            }
        }

        return self::uploadedFileArrayTrim($specTree);
    }

    /**
     * Find out how many levels of an array it has.
     *
     * @param array  $files Data structure from $_FILES.
     * @param string $attr  The attributes of a file.
     *
     * @return array
     */
    public static function uploadedFileNestedFields(array $files, string $attr): array
    {
        $result = [];
        $values = $files;

        if (isset($files[$attr])) {
            $values = $files[$attr];
        }

        foreach ($values as $key => $value) {

            /**
             * Hereby to add `_` to be a part of the key for letting `array_merge_recursive`
             * method can deal with numeric keys as string keys. 
             * It will be restored in the next step.
             *
             * @see uploadedFileArrayTrim
             */
            if (is_numeric($key)) {
                $key .= '_';
            }

            if (is_array($value)) {
                $result[$key] = self::uploadedFileNestedFields($value, $attr);
            } else {
                $result[$key][$attr] = $value;
            }
        }

        return $result;
    }

    /**
     * That's because that PHP function `array_merge_recursive` has the different
     * results as dealing with string keys and numeric keys.
     * In the previous step, we made numeric keys to stringify, so that we want to
     * restore them back to numeric ones.
     *
     * @param array|string $values
     *
     * @return array|string
     */
    public static function uploadedFileArrayTrim($values)
    {
        $result = [];

        if (is_array($values)) {

            foreach($values as $key => $value) {

                // Restore the keys back to the original ones.
                $key = rtrim($key, '_');

                if (is_array($value)) {
                    $result[$key] = self::uploadedFileArrayTrim($value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Convert the parse-ready array into PSR-7 specs.
     *
     * @param string|array $values
     * 
     * @return array
     */
    public static function uploadedFileSpecsConvert($values) 
    {
        $result = [];

        if (is_array($values)) {

            foreach ($values as $key => $value) {

                if (is_array($value)) {

                    // Continue querying self, until a string is found.
                    $result[$key] = self::uploadedFileSpecsConvert($value);

                } elseif ($key === 'tmp_name') {

                    /**
                     * Once one of the keys on the same level has been found,
                     * then we can fetch the others at a time.
                     * In this case, the `tmp_name` found.
                     */
                    $result = new uploadedFile(
                        $values['tmp_name'],
                        $values['name'],
                        $values['type'],
                        $values['size'],
                        $values['error']
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Check out whether an array is compatible to PSR-7 file structure.
     * 
     * @param array $values The array to check.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function assertUploadedFiles(array $values): void
    {
        if (is_array($values)) {
            foreach ($values as $value) {
                if (is_array($value)) {
                    $this->assertUploadedFiles($value);
                } elseif (! ($value instanceof UploadedFileInterface)) {
                    throw new InvalidArgumentException(
                        'Invalid PSR-7 array structure for handling UploadedFile.'
                    );
                }
            }
        }
    }

    /**
     * Throw an exception if an unsupported argument type is provided.
     * 
     * @param string|array|null $data The deserialized body data. This will
     *     typically be in an array or object.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function assertParsedBody($data): void
    {
        if (
            ! is_null($data) &&
            ! is_array($data) && 
            ! is_object($data)
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Only accepts array, object and null, but %s provided.',
                    gettype($data)
                )
            );
        }
    }
}
