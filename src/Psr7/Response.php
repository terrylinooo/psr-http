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

use Psr\Http\Message\ResponseInterface;
use Shieldon\Psr7\Message;
use InvalidArgumentException;

use function gettype;
use function is_integer;
use function is_string;
use function sprintf;
use function str_replace;
use function strpos;

/*
 * Representation of an outgoing, server-side response.
 */
class Response extends Message implements ResponseInterface
{
    /**
     * HTTP status number.
     *
     * @var int
     */
    protected $status;

    /**
     * HTTP status reason phrase.
     *
     * @var string
     */
    protected $reasonPhrase;

    /**
     * HTTP status codes.
     *
     * @see https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @var array
     */
    protected static $statusCode = [

        // 1xx: Informational
        // Request received, continuing process.
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        // 2xx: Success
        // The action was successfully received, understood, and accepted.
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',

        // 3xx: Redirection
        // Further action must be taken in order to complete the request.
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        //  => '309-399	Unassigned.'

        // 4xx: Client Error
        // The request contains bad syntax or cannot be fulfilled.
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        //  => '418-412: Unassigned'
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        //  =>  '432-450: Unassigned.'
        451 => 'Unavailable For Legal Reasons',
        //  =>  '452-499: Unassigned.'

        // 5xx: Server Error
        // The server failed to fulfill an apparently valid request.
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        //  => '512-599	Unassigned.'
    ];

    /**
     * Response Constructor.
     *
     * @param int                    $status  Response HTTP status code.
     * @param array                  $headers Response headers.
     * @param StreamInterface|string $body    Response body.
     * @param string                 $version Response protocol version.
     * @param string                 $reason  Reaspnse HTTP reason phrase.
     */
    public function __construct(
        int    $status  = 200  ,
        array  $headers = []   ,
               $body    = ''   ,
        string $version = '1.1',
        string $reason  = 'OK'
    ) {
        $this->assertStatus($status);
        $this->assertReasonPhrase($reason);
        $this->assertProtocolVersion($version);

        $this->setHeaders($headers);
        $this->setBody($body);

        $this->status = $status;
        $this->protocolVersion = $version;
        $this->reasonPhrase = $reason;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $this->assertStatus($code);
        $this->assertReasonPhrase($reasonPhrase);

        if ($reasonPhrase === '' && isset(self::$statusCode[$code])) {
            $reasonPhrase = self::$statusCode[$code];
        }

        $clone = clone $this;
        $clone->status = $code;
        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /*
    |--------------------------------------------------------------------------
    | Non PSR-7 Methods.
    |--------------------------------------------------------------------------
    */

    /**
     * Throw exception when the HTTP status code is not valid.
     *
     * @param int $code HTTP status code.
     *
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertStatus($code)
    {
        if (!is_integer($code)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Status code should be an integer value, but "%s" provided.',
                    gettype($code)
                )
            );
        }

        if (!($code > 100 && $code < 599)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Status code should be in a range of 100-599, but "%s" provided.',
                    $code
                )
            );
        }
    }

    /**
     * Throw exception when the HTTP reason phrase is not valid.
     *
     * @param string $reasonPhrase
     * 
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    protected function assertReasonPhrase($reasonPhrase)
    {
        if ($reasonPhrase === '') {
            return;
        }

        if (!is_string($reasonPhrase)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Reason phrase must be a string, but "%s" provided.',
                    gettype($reasonPhrase)
                )
            );
        }

        // Special characters, such as "line breaks", "tab" and others...
        $escapeCharacters = [
            '\f', '\r', '\n', '\t', '\v', '\0', '[\b]', '\s', '\S', '\w', '\W', '\d', '\D', '\b', '\B', '\cX', '\xhh', '\uhhhh'
        ];

        $filteredPhrase = str_replace($escapeCharacters, '', $reasonPhrase);

        if ($reasonPhrase !== $filteredPhrase) {
            foreach ($escapeCharacters as $escape) {
                if (strpos($reasonPhrase, $escape) !== false) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Reason phrase contains "%s" that is considered as a prohibited character.',
                            $escape
                        )
                    );
                }
            }
        }
    }
}
