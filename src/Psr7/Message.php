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

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use Shieldon\Psr7\Stream;
use InvalidArgumentException;

use function array_map;
use function array_merge;
use function count;
use function fopen;
use function fseek;
use function fwrite;
use function gettype;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_integer;
use function is_scalar;
use function is_string;
use function preg_match;
use function preg_match_all;
use function sprintf;
use function strtolower;
use function trim;

use const PREG_SET_ORDER;

/**
 * HTTP messages consist of requests from a client to a server and responses
 * from a server to a client.
 */
class Message implements MessageInterface
{
    /**
     * A HTTP protocol version number.
     *
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * An instance with the specified message body.
     *
     * @var StreamInterface
     */
    protected $body;

    /**
     * An array of mapping header information with `string => array[]` format.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * A map of header name for lower case and original case.
     * In `lower => original` format.
     *
     * @var array
     */
    protected $headerNameMapping = [];

    /**
     * Valid HTTP version numbers.
     *
     * @var array
     */
    protected $validProtocolVersions = [
        '1.0',
        '1.1',
        '2.0',
        '3.0',
    ];

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version): MessageInterface
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        $headers = $this->headers;

        foreach ($this->headerNameMapping as $origin) {
            $name = strtolower($origin);
            if (isset($headers[$name])) {
                $value = $headers[$name];
                unset($headers[$name]);
                $headers[$origin] = $value;
            }
        }

        return $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name): bool
    {
        $name = strtolower($name);

        return isset($this->headers[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name): array
    {
        $name = strtolower($name);

        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value): MessageInterface
    {
        $origName = $name;

        $name = $this->normalizeHeaderFieldName($name);
        $value = $this->normalizeHeaderFieldValue($value);

        $clone = clone $this;
        $clone->headers[$name] = $value;
        $clone->headerNameMapping[$name] = $origName;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value): MessageInterface
    {
        $origName = $name;

        $name = $this->normalizeHeaderFieldName($name);
        $value = $this->normalizeHeaderFieldValue($value);

        $clone = clone $this;
        $clone->headerNameMapping[$name] = $origName;

        if (isset($clone->headers[$name])) {
            $clone->headers[$name] = array_merge($this->headers[$name], $value);
        } else {
            $clone->headers[$name] = $value;
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name): MessageInterface
    {
        $origName = $name;
        $name = strtolower($name);

        $clone = clone $this;
        unset($clone->headers[$name]);
        unset($clone->headerNameMapping[$name]);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Set headers to property $headers.
     *
     * @param array $headers A collection of header information.
     *
     * @return void
     */
    protected function setHeaders(array $headers): void
    {
        $arr = [];
        $origArr = [];

        foreach ($headers as $name => $value) {
            $origName = $name;
            $name = $this->normalizeHeaderFieldName($name);
            $value = $this->normalizeHeaderFieldValue($value);
  
            $arr[$name] = $value;
            $origArr[$name] = $origName;
        }

        $this->headers = $arr;
        $this->headerNameMapping = $origArr;
    }

    /**
     * Set the request body.
     *
     * This method only provides two types of input, string and StreamInterface
     *
     * String          - As a simplest way to initialize a stream resource.
     * StreamInterface - If you would like to use stream resource its mode is
     *                   not "r+", you should create a Stream instance by 
     *                   yourself.
     *
     * @param string|StreamInterface $body Request body
     *
     * @return void
     */
    protected function setBody($body): void
    {
        if ($body instanceof StreamInterface) {
            $this->body = $body;

        } elseif (is_string($body)) {
            $resource = fopen('php://temp', 'r+');

            if ($body !== '') {
                fwrite($resource, $body);
                fseek($resource, 0);
            }

            $this->body = new Stream($resource);
        }
    }

    /**
     * Parse raw header text into an associated array.
     *
     * @param string $message Raw header text.
     *
     * @return array
     */
    public static function parseRawHeader(string $message): array
    {
        preg_match_all('/^([^:\n]*): ?(.*)$/m', $message, $headers, PREG_SET_ORDER);

        $num = count($headers);

        if ($num > 1) {
            $headers = array_merge(...array_map(function($line) {
                $name = trim($line[1]);
                $field = trim($line[2]);
                return [$name => $field];
            }, $headers));

            return $headers;

        } elseif ($num === 1) {
            $name = trim($headers[0][1]);
            $field = trim($headers[0][2]);
            return [$name => $field];
        }

        return [];
    }

    /**
     * Normalize the header field name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeHeaderFieldName($name): string
    {
        $this->assertHeaderFieldName($name);
        
        return trim(strtolower($name));
    }

    /**
     * Normalize the header field value.
     *
     * @param mixed $value
     * 
     * @return mixed
     */
    protected function normalizeHeaderFieldValue($value)
    {
        $this->assertHeaderFieldValue($value);

        $result = false;

        if (is_string($value)) {
            $result = [trim($value)];

        } elseif (is_array($value)) {
            foreach ($value as $v) {
                if (is_string($v)) {
                    $value[] = trim($v);
                }
            }
            $result = $value;

        } elseif (is_float($value) || is_integer($value)) {
            $result = [(string) $value];
        }

        return $result;
    }

    /**
     * Throw exception if the header is not compatible with RFC 7230.
     * 
     * @param string $name The header name.
     *
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertHeaderFieldName($name): void
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Header field name must be a string, but "%s" given.',
                    gettype($name)
                )
            );
        }
        // see https://tools.ietf.org/html/rfc7230#section-3.2.6
        // alpha  => a-zA-Z
        // digit  => 0-9
        // others => !#$%&\'*+-.^_`|~

        if (!preg_match('/^[a-zA-Z0-9!#$%&\'*+-.^_`|~]+$/', $name)) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not valid header name, it must be an RFC 7230 compatible string.',
                    $name
                )
            );
        }
    }

    /**
     * Throw exception if the header is not compatible with RFC 7230.
     * 
     * @param array|null $value The header value.
     *
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertHeaderFieldValue($value = null): void
    {
        if (is_scalar($value) && !is_bool($value)) {
            $value = [(string) $value];
        }

        if (empty($value)) {
            throw new InvalidArgumentException(
                'Empty array is not allowed.'
            );
        }

        if (is_array($value)) {
            foreach ($value as $item) {

                if ($item === '') {
                    return;
                }

                if (!is_scalar($item) || is_bool($item)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'The header values only accept string and number, but "%s" provided.',
                            gettype($item)
                        )
                    );
                }

                // https://www.rfc-editor.org/rfc/rfc7230.txt (page.25)
                // field-content = field-vchar [ 1*( SP / HTAB ) field-vchar ]
                // field-vchar   = VCHAR / obs-text
                // obs-text      = %x80-FF
                // SP            = space
                // HTAB          = horizontal tab
                // VCHAR         = any visible [USASCII] character. (x21-x7e)
                // %x80-FF       = character range outside ASCII.

                // I THINK THAT obs-text SHOULD N0T BE USED.
                // OR EVEN I CAN PASS CHINESE CHARACTERS, THAT'S WEIRD.
                if (!preg_match('/^[ \t\x21-\x7e]+$/', $item)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            '"%s" is not valid header value, it must contains visible ASCII characters only.',
                            $item
                        )
                    );
                }
            }
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The header field value only accepts string and array, but "%s" provided.',
                    gettype($value)
                )
            );
        }
    }

    /**
     * Check out whether a protocol version number is supported.
     *
     * @param string $version HTTP protocol version.
     * 
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertProtocolVersion(string $version): void
    {
        if (!in_array($version, $this->validProtocolVersions)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported HTTP protocol version number. "%s" provided.',
                    $version
                )
            );
        }
    }
}
