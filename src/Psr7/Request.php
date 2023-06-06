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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Shieldon\Psr7\Message;
use Shieldon\Psr7\Uri;
use InvalidArgumentException;

use function in_array;
use function is_string;
use function preg_match;
use function sprintf;
use function strtoupper;

/*
 * Representation of an outgoing, client-side request.
 */
class Request extends Message implements RequestInterface
{
    /**
     * The HTTP method of the outgoing request.
     *
     * @var string
     */
    protected $method;

    /**
     * The target URL of the outgoing request.
     *
     * @var string
     */
    protected $requestTarget;

    /**
     * A UriInterface object.
     *
     * @var UriInterface
     */
    protected $uri;


    /**
     * Valid HTTP methods.
     *
     * @ref http://tools.ietf.org/html/rfc7231
     *
     * @var array
     */
    protected $validMethods = [

        // The HEAD method asks for a response identical to that of a GET
        // request, but without the response body.
        'HEAD',

        // The GET method requests a representation of the specified 
        // resource. Requests using GET should only retrieve data.
        'GET',

        // The POST method is used to submit an entity to the specified 
        // resource, often causing a change in state or side effects on the
        // server.
        'POST', 
        
        // The PUT method replaces all current representations of the target
        // resource with the request payload.
        'PUT', 

        // The DELETE method deletes the specified resource.
        'DELETE',

        // The PATCH method is used to apply partial modifications to a 
        // resource.
        'PATCH',

        // The CONNECT method establishes a tunnel to the server identified
        // by the target resource.
        'CONNECT',

        //The OPTIONS method is used to describe the communication options
        // for the target resource.
        'OPTIONS',

        // The TRACE method performs a message loop-back test along the
        // path to the target resource.
        'TRACE',
    ];

    /**
     * Request constructor.
     *
     * @param string                 $method  Request HTTP method
     * @param string|UriInterface    $uri     Request URI
     * @param string|StreamInterface $body    Request body - see setBody()
     * @param array                  $headers Request headers
     * @param string                 $version Request protocol version
     */
    public function __construct(
        string $method  = 'GET',
        $uri            = ''   ,
        $body           = ''   ,
        array  $headers = []   ,
        string $version = '1.1'
    ) {
        $this->method = $method;

        $this->assertMethod($method);
  
        $this->assertProtocolVersion($version);
        $this->protocolVersion = $version;

        if ($uri instanceof UriInterface) {
            $this->uri = $uri;

        } elseif (is_string($uri)) {
            $this->uri = new Uri($uri);

        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'URI should be a string or an instance of UriInterface, but "%s" provided.',
                    gettype($uri)
                )
            );
        }

        $this->setBody($body);
        $this->setHeaders($headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget) {
            return $this->requestTarget;
        }

        $path = $this->uri->getPath();
        $query = $this->uri->getQuery();

        if (empty($path)) {
            $path = '/';
        }

        if (!empty($query)) {
            $path .= '?' . $query;
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget): RequestInterface
    {
        if (!is_string($requestTarget)) {
            throw new InvalidArgumentException(
                'A request target must be a string.'
            );
        }

        if (preg_match('/\s/', $requestTarget)) {
            throw new InvalidArgumentException(
                'A request target cannot contain any whitespace.'
            );
        }

        $clone = clone $this;
        $clone->requestTarget = $requestTarget;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method): RequestInterface
    {
        $this->assertMethod($method);

        $clone = clone $this;
        $clone->method = $method;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        $host = $uri->getHost();

        $clone = clone $this;
        $clone->uri = $uri;

        if (
            // This method MUST update the Host header of the returned request by
            // default if the URI contains a host component.
            (!$preserveHost && $host !== '') ||

            // When `$preserveHost` is set to `true`.
            // If the Host header is missing or empty, and the new URI contains
            // a host component, this method MUST update the Host header in the returned
            // request.
            ($preserveHost && !$this->hasHeader('Host') && $host !== '')
        ) {
            $headers = $this->getHeaders();
            $headers['host'] = $host;
            $clone->setHeaders($headers);
        }

        return $clone;
    }

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Check out whether a method defined in RFC 7231 request methods.
     *
     * @param string $method Http methods
     * 
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertMethod($method): void
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException(
                sprintf(
                    'HTTP method must be a string.',
                    $method
                )
            );
        }

        if (!in_array(strtoupper($this->method), $this->validMethods)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported HTTP method. It must be compatible with RFC-7231 request method, but "%s" provided.',
                    $method
                )
            );
        }
    }
}
