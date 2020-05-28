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

use Psr\Http\Message\UriInterface;

/*
 * Value object representing a URI.
 */
class Uri implements UriInterface
{
    /**
     * The scheme component of the URI.
     * For example, https://terryl.in/
     * In this case, "https" is the scheme.
     * 
     * @var string
     */
    protected $scheme = '';

    /**
     * The user component of the URI.
     * For example, https://jack:1234@terryl.in
     * In this case, "jack" is the user.
     *
     * @var string
     */
    protected $user = '';

    /**
     * The password component of the URI.
     * For example, http://jack:1234@terryl.in
     * In this case, "1234" is the password.
     *
     * @var string
     */
    protected $password = '';

    /**
     * The host component of the URI.
     * For example, https://terryl.in:443/zh/
     * In this case, "terryl.in" is the host.
     *
     * @var string
     */
    protected $host = '';

    /**
     * The port component of the URI.
     * For example, https://terryl.in:443
     * In this case, "443" is the port.
     * 
     * @var null|int
     */
    protected $port;

    /**
     * The path component of the URI.
     * For example, https://terryl.in/zh/?paged=2
     * In this case, "/zh/" is the path.
     *
     * @var string
     */
    protected $path = '';

    /**
     * The query component of the URI.
     * For example, https://terryl.in/zh/?paged=2
     * In this case, "paged=2" is the query.
     *
     * @var string
     */
    protected $query = '';

    /**
     * The fragment component of the URI.
     * For example, https://terryl.in/#main-container
     * In this case, "main-container" is the fragment.
     *
     * @var string
     */
    protected $fragment = '';

    /**
     * Uri constructor.
     */
    public function __construct($uri = '')
    {
        if ($uri !== '') {
            $this->assetValidUri($uri);

            // Todo
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        $authority = $this->host;

        if ($this->getUserInfo() !== '') {
            $authority = $this->host . '@' . $authority;
        }

        if ($this->port !== '') {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        $userInfo = $this->user;

        if (! empty($this->password)) {
            $userInfo .= ':' . $this->password;
        }

        return $userInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        $this->assertScheme($scheme);

        $clone = clone $this;
        $clone->scheme = $scheme;
        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null)
    {
        $password = (string) $password;

        $this->assertString($user, 'user');
        $this->assertString($password, 'password');

        $clone = clone $this;
        $clone->user = $user;
        $clone->password = $password;

        if ($user === '') {
            $clone->password = '';
        }

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        $this->assertHost($host);

        $clone = clone $this;
        $clone->host = $host;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        $this->assertPort($port);

        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        $this->assertString($path, 'path');

        $clone = clone $this;
        $clone->path = '/' . ltrim(rawurlencode($path), '/');

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        $this->assertString($query, 'query');

        $clone = clone $this;
        $clone->fragment = ltrim(rawurlencode($query), '?');

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        $this->assertString($fragment, 'fragment');

        $clone = clone $this;
        $clone->fragment = rawurlencode($fragment);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $uri = '';

        // If a scheme is present, it MUST be suffixed by ":".
        if (! empty($this->scheme)) {
            $uri .= $this->scheme . ':';
        }

        // If an authority is present, it MUST be prefixed by "//".
        if (! empty($this->authority)) {
            $uri .= '//' . $this->authority;
        }

        // If the path is rootless and an authority is present, the path MUST
        // be prefixed by "/".
        $uri .= '/' . ltrim($this->path, '/');

        // If a query is present, it MUST be prefixed by "?".
        if (! empty($this->query)) {
            $uri .= '?' . $this->query;
        }

        // If a fragment is present, it MUST be prefixed by "#".
        if (! empty($this->fragment)) {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Throw exception for the invalid scheme.
     *
     * @param string $scheme The scheme string of a URI.
     *
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertScheme($scheme)
    {
        $this->assertString($scheme, 'scheme');

        $validSchemes = [
            0 => '',
            1 => 'http',
            2 => 'https',
        ];

        if (! in_array($scheme, $validSchemes)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The string "%s" is not a valid scheme.',
                    $scheme
                )
            );
        }
    }

    /**
     * Throw exception for the invalid query.
     *
     * @param string $query The query string to of a URI.
     *
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertQuery($query)
    {
        $this->assertString($query, 'query');
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
    protected function assertString($value, string $name = 'it')
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    ucfirst($name) . ' must be a string, but %s provided.',
                    gettype($value)
                )
            );
        }
    }

    /**
     * Throw exception for the invalid URI string.
     *
     * @param string $uri The URI string.
     * 
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertValidUri($uri)
    {
        $this->assertString($uri, 'uri');

        if (! filter_var($uri, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not a valid URI',
                    $uri
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
    protected function assertHost($host)
    {
        $this->assertString($host);

        if ($host === '') {
            // Note: An empty host value is equivalent to removing the host.
            // So that if the host is empty, ignore the following check.
            return;
        }

        if (! filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
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
    protected function assertPort($port)
    {
        if (
            ! is_null($port) || 
            ! is_integer($port)
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Port must be an integer or a null value, but %s provided.',
                    gettype($port)
                )
            );
        }

        if (! ($port < 65535 && $port > 0)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Port number should be in a range of 0-65535, but %s provided.',
                    $port
                )
            );
        }
    }
}
