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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Shieldon\Psr7\Request;
use Shieldon\Psr7\Utils\UploadedFileHelper;
use InvalidArgumentException;
use function file_get_contents;
use function gettype;
use function is_array;
use function is_null;
use function is_object;
use function json_decode;
use function json_last_error;
use function parse_str;
use function preg_split;
use function sprintf;
use function strtolower;
use function strtoupper;
use const JSON_ERROR_NONE;

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

        $this->determineParsedBody($postParams);

        // This property will be assigned to a parsed array that contains 
        // the UploadedFile instance(s) as the $filesParams is given.
        $this->uploadedFiles = [];

        if (!empty($filesParams)) {
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
    public function withCookieParams(array $cookies): ServerRequestInterface
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
    public function withQueryParams(array $query): ServerRequestInterface
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
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
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
    public function withParsedBody($data): ServerRequestInterface
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
    public function withAttribute($name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name): ServerRequestInterface
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
            } elseif (!($value instanceof UploadedFileInterface)) {
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

    /**
     * Confirm the content type and post values whether fit the requirement.
     *
     * @param array $postParams 
     * @return void
     */
    protected function determineParsedBody(array $postParams)
    {
        $headerContentType = $this->getHeaderLine('Content-Type');
        $contentTypeArr = preg_split('/\s*[;,]\s*/', $headerContentType);
        $contentType = strtolower($contentTypeArr[0]);
        $httpMethod = strtoupper($this->getMethod());

        // Is it a form submit or not.
        $isForm = false;

        if ($httpMethod === 'POST') {

            // If the request Content-Type is either application/x-www-form-urlencoded
            // or multipart/form-data, and the request method is POST, this method MUST
            // return the contents of $_POST.
            $postRequiredContentTypes = [
                '', // For unit testing purpose.
                'application/x-www-form-urlencoded',
                'multipart/form-data',
            ];

            if (in_array($contentType, $postRequiredContentTypes)) {
                $this->parsedBody = $postParams ?? null;
                $isForm = true;
            }
        }

        // @codeCoverageIgnoreStart
        // Maybe other http methods such as PUT, DELETE, etc...
        if ($httpMethod !== 'GET' && !$isForm) {

            // If it a JSON formatted string?
            $isJson = false;

            // Receive content from PHP stdin input, if exists.
            $rawText = file_get_contents('php://input');

            if (!empty($rawText)) {

                if ($contentType === 'application/json') {
                    $jsonParsedBody = json_decode($rawText);
                    $isJson = (json_last_error() === JSON_ERROR_NONE);
                }

                // Condition 1 - It's a JSON, now the body is a JSON object.
                if ($isJson) {
                    $this->parsedBody = $jsonParsedBody ?: null;
                }

                // Condition 2 - It's not a JSON, might be a http build query.
                if (!$isJson) {
                    parse_str($rawText, $parsedStr);
                    $this->parsedBody = $parsedStr ?: null;
                }
            }
        }

        // This part is manually tested by using PostMan.
        // @codeCoverageIgnoreEnd
    }
}
