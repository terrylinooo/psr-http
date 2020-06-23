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
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Shieldon\Psr7\Request;
use Shieldon\Psr7\Utils\UploadedFileHelper;
use InvalidArgumentException;

use function is_array;
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
    protected $attributes;

    /**
     * ServerRequest constructor.
     *
     * @param string                 $method       Request HTTP method
     * @param string|UriInterface    $uri          Request URI object URI or URL
     * @param string|StreamInterface $body         Request body
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
        $this->queryParams  = $getParams;
        $this->attributes   = [];

        if (!empty($postParams)) {
            $this->parsedBody = $postParams;
        }

        // This property will be assigned to a parsed array that contains 
        // the UploadedFile instance(s) as the $filesParams is given.
        $this->uploadedFiles = [];

        if (! empty($filesParams)) {
            $this->uploadedFiles = UploadedFileHelper::uploadedFileSpecsConvert(
                UploadedFileHelper::uploadedFileParse($filesParams)
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
                    'Only accepts array, object and null, but "%s" provided.',
                    gettype($data)
                )
            );
        }
    }
}
