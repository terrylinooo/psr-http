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

use Psr\Http\Message\UriInterface;
use InvalidArgumentException;

use function filter_var;
use function gettype;
use function is_integer;
use function is_null;
use function is_string;
use function ltrim;
use function parse_url;
use function rawurlencode;
use function sprintf;

/*
 * Value object representing a URI.
 */
class Uri implements UriInterface
{
    /**
     *    foo://example.com:8042/over/there?name=ferret#nose
     *    \_/   \______________/\_________/ \_________/ \__/
     *     |           |            |            |        |
     *  scheme     authority       path        query   fragment
     */

    /**
     * The scheme component of the URI.
     * For example, https://terryl.in/
     * In this case, "https" is the scheme.
     * 
     * @var string
     */
    protected $scheme;

    /**
     * The user component of the URI.
     * For example, https://jack:1234@terryl.in
     * In this case, "jack" is the user.
     *
     * @var string
     */
    protected $user;

    /**
     * The password component of the URI.
     * For example, http://jack:1234@terryl.in
     * In this case, "1234" is the password.
     *
     * @var string
     */
    protected $pass;

    /**
     * The host component of the URI.
     * For example, https://terryl.in:443/zh/
     * In this case, "terryl.in" is the host.
     *
     * @var string
     */
    protected $host;

    /**
     * The port component of the URI.
     * For example, https://terryl.in:443
     * In this case, "443" is the port.
     * 
     * @var int|null
     */
    protected $port;

    /**
     * The path component of the URI.
     * For example, https://terryl.in/zh/?paged=2
     * In this case, "/zh/" is the path.
     *
     * @var string
     */
    protected $path;

    /**
     * The query component of the URI.
     * For example, https://terryl.in/zh/?paged=2
     * In this case, "paged=2" is the query.
     *
     * @var string
     */
    protected $query;

    /**
     * The fragment component of the URI.
     * For example, https://terryl.in/#main-container
     * In this case, "main-container" is the fragment.
     *
     * @var string
     */
    protected $fragment;

    /**
     * Uri constructor.
     * 
     * @param string $uri The URI.
     */
    public function __construct($uri = '')
    {
        $this->assertString($uri, 'uri');
        $this->init((array) parse_url($uri));
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority(): string
    {
        $authority = '';

        if ($this->getUserInfo()) {
            $authority .= $this->getUserInfo() . '@';
        }

        $authority .= $this->getHost();

        if (!is_null($this->getPort())) {
            $authority .= ':' . $this->getPort();
        }

        return $authority;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo(): string
    {
        $userInfo = $this->user;

        if ($this->pass !== '') {
            $userInfo .= ':' . $this->pass;
        }

        return $userInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme): UriInterface
    {
        $this->assertScheme($scheme);

        $scheme = $this->filter('scheme', ['scheme' => $scheme]);

        $clone = clone $this;
        $clone->scheme = $scheme;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $pass = null): UriInterface
    {
        $this->assertString($user, 'user');
        $user = $this->filter('user', ['user' => $user]);

        if ($pass) {
            $this->assertString($pass, 'pass');
            $pass = $this->filter('pass', ['pass' => $pass]);
        }

        $clone = clone $this;
        $clone->user = $user;
        $clone->pass = $pass;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host): UriInterface
    {
        $this->assertHost($host);

        $host = $this->filter('host', ['host' => $host]);

        $clone = clone $this;
        $clone->host = $host;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port): UriInterface
    {
        $this->assertPort($port);

        $port = $this->filter('port', ['port' => $port]);

        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path): UriInterface
    {
        $this->assertString($path, 'path');

        $path = $this->filter('path', ['path' => $path]);

        $clone = clone $this;
        $clone->path = '/' . rawurlencode(ltrim($path, '/'));

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query): UriInterface
    {
        $this->assertString($query, 'query');

        $query = $this->filter('query', ['query' => $query]);

        // & => %26
        // ? => %3F

        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment): UriInterface
    {
        $this->assertString($fragment, 'fragment');

        $fragment = $this->filter('fragment', ['fragment' => $fragment]);

        $clone = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $uri = '';

        // If a scheme is present, it MUST be suffixed by ":".
        if ($this->getScheme() !== '') {
            $uri .= $this->getScheme() . ':';
        }

        // If an authority is present, it MUST be prefixed by "//".
        if ($this->getAuthority() !== '') {
            $uri .= '//' . $this->getAuthority();
        }

        // If the path is rootless and an authority is present, the path MUST
        // be prefixed by "/".
        $uri .= '/' . ltrim($this->getPath(), '/');

        // If a query is present, it MUST be prefixed by "?".
        if ($this->getQuery() !== '') {
            $uri .= '?' . $this->getQuery();
        }

        // If a fragment is present, it MUST be prefixed by "#".
        if ($this->getFragment() !== '') {
            $uri .= '#' . $this->getFragment();
        }

        return $uri;
    }

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Initialize.
     *
     * @param array $data Parsed URL data.
     *
     * @return void
     */
    protected function init(array $data = []): void
    {
        $components = [
            'scheme',
            'user',
            'pass',
            'host',
            'port',
            'path',
            'query',
            'fragment'
        ];

        foreach ($components as $v) {
            $this->{$v} = $this->filter($v, $data);
        }
    }

    /**
     * Filter URI components.
     * 
     * Users can provide both encoded and decoded characters.
     * Implementations ensure the correct encoding as outlined.
     * @see https://tools.ietf.org/html/rfc3986#section-2.2
     *
     * @param string $key  The part of URI.
     * @param array  $data Data parsed from a given URL.
     *
     * @return string|int|null
     */
    protected function filter(string $key, $data)
    {
        $notExists = [
            'scheme' => '',
            'user' => '',
            'pass' => '',
            'host' => '',
            'port' => null,
            'path' => '',
            'query' => '',
            'fragment' => '',
        ];

        if (!isset($data[$key])) {
            return $notExists[$key];
        }

        $value = $data[$key];
         
        // gen-delims  = ":" / "/" / "?" / "#" / "[" / "]" / "@"
        // $genDelims = ':/\?#\[\]@';
 
        // sub-delims  = "!" / "$" / "&" / "'" / "(" / ")"
        //             / "*" / "+" / "," / ";" / "="
        $subDelims = '!\$&\'\(\)\*\+,;=';

        // $unreserved  = ALPHA / DIGIT / "-" / "." / "_" / "~"
        $unReserved = 'a-zA-Z0-9\-\._~';

        // Encoded characters, such as "?" encoded to "%3F".
        $encodePattern = '%(?![A-Fa-f0-9]{2})';

        $regex = '';

        switch ($key) {
            case 'host':
            case 'scheme':
                return strtolower($value);
                break;

            case 'query':
            case 'fragment':
                $specPattern = '%:@\/\?';
                $regex = '/(?:[^' . $unReserved . $subDelims . $specPattern . ']+|' . $encodePattern . ')/';
                break;

            case 'path':
                $specPattern = '%:@\/';
                $regex = '/(?:[^' . $unReserved . $subDelims . $specPattern . ']+|' . $encodePattern . ')/';
                break;

            case 'user':
            case 'pass':
                $regex = '/(?:[^%' . $unReserved . $subDelims . ']+|' . $encodePattern . ')/';
                break;

            case 'port':
                if ($this->scheme === 'http' && (int) $value !== 80) {
                    return (int) $value;
                }
                if ($this->scheme === 'https' && (int) $value !== 443) {
                    return (int) $value;
                } 
                if ($this->scheme === '') {
                    return (int) $value;
                }
                return null;

            // endswitch
        }

        if ($regex) {
            return preg_replace_callback(
                $regex,
                function ($match) {
                    return rawurlencode($match[0]);
                },
                $value
            );
        }

        // @codeCoverageIgnoreStart

        return $value;

        // @codeCoverageIgnoreEnd
    }

    /**
     * Throw exception for the invalid scheme.
     *
     * @param string $scheme The scheme string of a URI.
     *
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertScheme($scheme): void
    {
        $this->assertString($scheme, 'scheme');

        $validSchemes = [
            0 => '',
            1 => 'http',
            2 => 'https',
        ];

        if (!in_array($scheme, $validSchemes)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The string "%s" is not a valid scheme.',
                    $scheme
                )
            );
        }
    }

    /**
     * Throw exception for the invalid value.
     *
     * @param string $value The value to check.
     * @param string $name  The name of the value.
     *
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertString($value, string $name = 'it'): void
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    ucfirst($name) . ' must be a string, but %s provided.',
                    gettype($value)
                )
            );
        }
    }

    /**
     * Throw exception for the invalid host string.
     *
     * @param string $host The host string to of a URI.
     * 
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertHost($host): void
    {
        $this->assertString($host);

        if (empty($host)) {
            // Note: An empty host value is equivalent to removing the host.
            // So that if the host is empty, ignore the following check.
            return;
        }

        if (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not a valid host',
                    $host
                )
            );
        }
    }

    /**
     * Throw exception for the invalid port.
     *
     * @param null|int $port The port number to of a URI.
     * 
     * @return void
     *
     * @throws InvalidArgumentException
     */
    protected function assertPort($port): void
    {
        if (
            !is_null($port) && 
            !is_integer($port)
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Port must be an integer or a null value, but %s provided.',
                    gettype($port)
                )
            );
        }

        if (!($port > 0 && $port < 65535)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Port number should be in a range of 0-65535, but %s provided.',
                    $port
                )
            );
        }
    }
}
