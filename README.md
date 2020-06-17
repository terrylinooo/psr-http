# PHP PSR-7, PSR-15, PSR-17 Implementation and Examples

[![Build Status](https://travis-ci.org/terrylinooo/psr-http.svg?branch=master)](https://travis-ci.org/terrylinooo/psr-http) [![codecov](https://img.shields.io/codecov/c/github/terrylinooo/psr-http.svg)](https://codecov.io/gh/terrylinooo/psr-http) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

This library is a PSR HTTP implementation with detailed examples, following up the PSR-7 ([HTTP Message Interfaces](https://www.php-fig.org/psr/psr-7/)), PSR-15 ([HTTP Server Request Handlers](https://www.php-fig.org/psr/psr-15/)) and PSR-17 ([HTTP Factories](https://www.php-fig.org/psr/psr-17/)) documents. You can use it on any framework which is compatible with those PSR standards.

## Install

```php
composer require shieldon/psr-http
```

## Run test
```bash
composer install
composer test
```

## Table of Contents

- **PSR-17**: *HTTP Factories*
    - [RequestFactory](https://github.com/terrylinooo/psr-http#requestfactory)
    - [ServerRequestFactory](https://github.com/terrylinooo/psr-http#serverrequestfactory)
    - [ResponseFactory](https://github.com/terrylinooo/psr-http#responsefactory)
    - [StreamFactory](https://github.com/terrylinooo/psr-http#streamfactory)
    - [UploadedFileFactory](https://github.com/terrylinooo/psr-http#uploadedfilefactory)
    - [UriFactory](https://github.com/terrylinooo/psr-http#urifactory)
- **PSR-7**: *HTTP Message Interfaces*
    - [Message](https://github.com/terrylinooo/psr-http#message)
    - [Request](https://github.com/terrylinooo/psr-http#request) *(externds Message)*
    - [ServerRequest](https://github.com/terrylinooo/psr-http#serverrequest) *(externds Request)*
    - [Response](https://github.com/terrylinooo/psr-http#response)
    - [Stream](https://github.com/terrylinooo/psr-http#stream)
    - [UploadedFile](https://github.com/terrylinooo/psr-http#uploadedfile)
    - [Uri](https://github.com/terrylinooo/psr-http#uri)
- **PSR-15**: *HTTP Server Request Handlers*
    - [RequestHandler](https://github.com/terrylinooo/psr-http#requesthandler)
    - [Middleware](https://github.com/terrylinooo/psr-http#middleware)


The Shieldon HTTP implementation requires at least PHP 7.1 to run.
The usages of every method can be found in the [unit tests](https://github.com/terrylinooo/psr-http/tree/master/tests/).

## PSR-17 Factories

- [RequestFactory](https://github.com/terrylinooo/psr-http#requestfactory)
- [ServerRequestFactory](https://github.com/terrylinooo/psr-http#serverrequestfactory)
- [ResponseFactory](https://github.com/terrylinooo/psr-http#responsefactory)
- [StreamFactory](https://github.com/terrylinooo/psr-http#streamfactory)
- [UploadedFileFactory](https://github.com/terrylinooo/psr-http#uploadedfilefactory)
- [UriFactory](https://github.com/terrylinooo/psr-http#urifactory)

Here are some examples that show you the way creating PSR-7 instances from PSR-17 HTTP factories.

### RequestFactory

- createRequest

#### __construct

None

#### createRequest(`$method`, `$uri`)

- ***param*** `string ` method `*` *The HTTP method associated with the request.*
- ***param*** `UriInterface|string` uri `*` *The URI associated with the request.*
- ***return*** `RequestInterface`

Example:

```php
use Shieldon\Psr17\RequestFactory;

$requestFactory = new RequestFactory();
$request = $requestFactory->createRequest('GET', 'https://www.google.com');
```

### ServerRequestFactory

- createServerRequest
- ::fromGlobal `(Non-PSR)`

#### __construct

None

Example:

```php
use Shieldon\Psr17\ServerRequestFactory;

$serverRequestFactory = new ServerRequestFactory();
```

#### createServerRequest(`$method`, `$uri`, `$serverParams`)

- ***param*** `string` method `*`*The HTTP method associated with the request.*
- ***param*** `UriInterface|string` uri `*`*The URI associated with the request.*
- ***param*** `array` serverParams `= []` *An array of Server API (SAPI) parameters with which to seed the generated request instance.*
- ***return*** `ServerRequestInterface`

Examples:

```php
$serverRequestFactory = new ServerRequestFactory();

$method = 'GET';
$url = 'https://www.yourwebsite.com/current-page/';

$serverRequest = $serverRequestFactory->createServerRequest($method, $uri);
```

#### ::fromGlobal()

 `(Non-PSR)`

- ***return*** `ServerRequestInterface`

Examples:

```php
$serverRequest = ServerRequestFactory::fromGlobal();
```

### ResponseFactory

- createResponse

#### __construct

None

#### createResponse(`$code`, `$reasonPhrase`)

- ***param*** `int` code `= 200` *The HTTP status code.*
- ***param*** `string` reasonPhrase `= ''` *The reason phrase to associate with the status code.*
- ***return*** `ResponseInterface`

Example:
```php
use Shieldon\Psr17\ResponseFactory;

$responseFactory = new ResponseFactory();
$response = $responseFactory->createResponse(200, 'OK');
```

### StreamFactory

- createStream
- createStreamFromFile
- createStreamFromResource

#### __construct

None

#### createStream(`$content`)

- ***param*** `string` content `= ""` *String content with which to populate the stream.*
- ***return*** `StreamInterface`

Example:

```php
$streamFactory = new StreamFactory();
$stream = $streamFactory->createStream('Foo Bar');

echo $stream;
// Outputs: Foo Bar
```

#### createStreamFromFile(`$filename`, `$mode`)

- ***param*** `string` filename `*` *The filename or stream URI to use as basis of stream.*
- ***param*** `string` mode `r` *The mode with which to open the underlying filename/stream.*
- ***return*** `StreamInterface`

Example:

```php
$sourceFile = BOOTSTRAP_DIR . '/sample/shieldon_logo.png';

$streamFactory = new StreamFactory();
$stream = $streamFactory->createStreamFromFile($sourceFile);

echo $stream->getSize();
// Outputs: 15166
```

#### createStreamFromResource(`$resource`)

- ***param*** `string` resource `*` *The PHP resource to use as the basis for the stream.*
- ***return*** `StreamInterface`

Example:

```php
$streamFactory = new StreamFactory();
$stream = $streamFactory->createStreamResource(
    fopen('php://temp', 'r+')
);
```

### UploadedFileFactory

- createUploadedFile

#### __construct

None

#### createUploadedFile(`$stream`, `$size`, `$error`, `$clientFilename`, `$clientMediaType`)

- ***param*** `StreamInterface` stream `*` *The underlying stream representing the uploaded file content.*
- ***param*** `int|null` size `= null` *The size of the file in bytes.*
- ***param*** `int` error `= 0` *The PHP file upload error.*
- ***param*** `string|null` clientFilename `= null` *The filename as provided by the client, if any.*
- ***param*** `string|null` clientMediaType `= null` *The media type as provided by the client, if any.*
- ***return*** `UploadedFileInterface`

Example:

```php
$uploadedFileFactory = new UploadedFileFactory();

$sourcePath = BOOTSTRAP_DIR . '/sample/shieldon_logo.png';
$targetPath = STORAGE_DIR . '/images/shieldon_logo.png';

$streamFactory = new StreamFactory();
$uploadedFileFactory = new UploadedFileFactory();

$stream = $streamFactory->createStreamFromFile($sourcePath);
$uploadedFile = $uploadedFileFactory->createUploadedFile($stream);

// Move file from $sourcePath to $targetPath.
$uploadedFile->moveTo($targetPath);
```

### UriFactory

- createUri
- ::fromGlobal  `(Non-PSR)`

#### __construct

None

#### createUri(`$uri`)

- ***param*** `string` uri `= ""` *The URI to parse.*
- ***return*** `UriInterface`

Example:

```php
$uriFactory = new UriFactory;
$uri = $uriFactory->createUri();
```

#### ::fromGlobal()

 `(Non-PSR)`

- ***return*** `UriInterface`
Examples:

```php
$uri = UriFactory::fromGlobal();
```

---

## PSR-7 Classes

- [Message](https://github.com/terrylinooo/psr-http#message)
- [Request](https://github.com/terrylinooo/psr-http#request) *(externds Message)*
- [ServerRequest](https://github.com/terrylinooo/psr-http#serverrequest) *(externds Request)*
- [Response](https://github.com/terrylinooo/psr-http#response)
- [Stream](https://github.com/terrylinooo/psr-http#stream)
- [UploadedFile](https://github.com/terrylinooo/psr-http#uploadedfile)
- [Uri](https://github.com/terrylinooo/psr-http#uri)

Note: 

Here only shows the PSR-7 methods because other non-PSR methods are just helpers. They are listed on the bottom of this page, you can check out them if you are interested.

---

### Message

- getProtocolVersion	
- withProtocolVersion	
- getHeaders	
- hasHeader	
- getHeader	
- getHeaderLine	
- withHeader	
- withAddedHeader	
- withoutHeader	
- getBody	
- withBody

#### __construct

None

Example:
```php
$message = new \Shieldon\Psr7\Message();
```

#### getProtocolVersion()

- ***return*** `string` *HTTP protocol version.*

Example:

```php
echo $message->getProtocolVersion();
// Outputs: 1.1
```

#### withProtocolVersion(`$version`)

- ***param*** `string` version `*` *HTTP protocol version.*
- ***return*** `static`

#### getHeaders()

- ***return*** `array` (`string[][]`) *Each key is a header name, and each value is an array of strings for that header*

Example:

```php
$headers = $message->getHeaders();

print(print_r($headers, true));

/* Outputs:

Array
(
    [user-agent] => Array
        (
            [0] => Mozilla/5.0 (Windows NT 10.0; Win64; x64)
        )

    [host] => Array
        (
            [0] => 127.0.0.1
        )

    [accept] => Array
        (
            [0] => text/html,application/xhtml+xml,application/xml;q=0.9
        )

    [accept_charset] => Array
        (
            [0] => ISO-8859-1,utf-8;q=0.7,*;q=0.3
        )
    [accept_language] => Array
        (
            [0] => en-US,en;q=0.9,zh-TW;q=0.8,zh;q=0.7
        )
)

*/
```

#### hasHeader(`$name`)

- ***param*** `string` name `*` *Case-insensitive header field name.*
- ***return*** `bool`

Example:
```php
if ($message->hasHeader('user-agent')) {
    echo 'Header user-agent exists.';
} else {
    echo 'Header user-agent does not exist.';
}
// Outputs: Header user-agent exists.
```

#### getHeader(`$name`)

- ***param*** `string` name `*` *Case-insensitive header field name.*
- ***return*** `array` *An array of string values as provided for the given. Return empty array if the header dosn't exist.*

Example:

```php
$useragent = $this->message->getHeader('user-agent');

print(print_r($useragent, true));

/* Outputs:

    Array
    (
        [0] => Mozilla/5.0 (Windows NT 10.0; Win64; x64)
    )

*/

$useragent = $this->message->getHeader('does-not-exist');

print(print_r($useragent, true));

/* Outputs:

    Array()

*/
```

#### getHeaderLine(`$name`)

- ***param*** `string` name `*` *Case-insensitive header field name.*
- ***return*** `array` *a string values as provided for the given header concatenated together using a comma. Return empty string if the header dosn't exist.*

Example:

```php
echo $this->message->getHeaderLine('user-agent');

// Outputs: Mozilla/5.0 (Windows NT 10.0; Win64; x64)
```

#### withHeader(`$name`, `$value`)

- ***param*** `string` name `*` *Case-insensitive header field name.*
- ***param*** `string|array` value `*` *Header value(s)*
- ***return*** `static`

Example:

```php
$message = $message->withHeader('foo', 'bar');

echo $message->getHeaderLine('foo');
// Outputs: bar

echo $message->getHeaderLine('FOO');
// Outputs: bar

$message = $message->withHeader('fOO', 'baz');
echo $message->getHeaderLine('foo');
// Outputs: baz

$message = $message->withHeader('fOO', ['bax', 'bay', 'baz']);
echo $message->getHeaderLine('foo');
// Outputs: bax, bay, baz
```

#### withAddedHeader(`$name`, `$value`)

- ***param*** `string` name `*` *Case-insensitive header field name.*
- ***param*** `string|array` value `*` *Header value(s)*
- ***return*** `static`

Existing values for the specified header will be maintained. The new value(s) will be appended to the existing list. If the header did not exist previously, it will be added.

Example:

```php
$message = $message->withHeader('foo', 'bar');

echo $message->getHeaderLine('foo');
// Outputs: bar

$message = $message->withAddedHeader('foo', 'baz');
echo $message->getHeaderLine('foo');
// Outputs: bar

$message = $message->withAddedHeader('foo2', 'baz');
echo $message->getHeaderLine('foo2');
// Outputs: baz
```

#### withoutHeader(`$name`)

- ***param*** `string` name `*` *Case-insensitive header field name.*
- ***return*** `static`

Example:

```php
$message = $message->withHeader('foo', 'bar');

echo $message->getHeaderLine('foo');
// Outputs: bar

$message = $message->withoutHeader('foo');
echo $message->getHeaderLine('foo');
// Outputs: 
```

#### getBody()

- ***return*** `StreamInterface`

Example:

```php
$stream = $message->getBody();

// Assume the content is a HTML formatted string.
// getContent() is a method defined in StreamInterface.
echo $stream->getContents();

// Outputs: <html>...</html>
```

#### withBody(`$body`)

- ***param*** `StreamInterface` body `*` *Body.*
- ***return*** `static`

Example:

```php

$stream = new \Shieldon\Psr7\Stream(fopen('php://temp', 'r+'));
$stream->write('Foo Bar');

$message = $message->withBody($stream);

echo $message->getBody()->getContents();

// Outputs: Foo Bar
```

---

### Request

- getRequestTarget	
- withRequestTarget	
- getMethod	
- withMethod	
- getUri	
- withUri

#### __construct(`$method`, `$uri`, `$body`, `$headers`, `$version`)

- ***param*** `string` method `= "GET"` *Request HTTP method.*
- ***param*** `string|UriInterface` uri `= ""` *Request URI object URI or URL.*
- ***param*** `string|StreamInterface` body `= ""` *Request body - see setBody()*
- ***param*** `array` headers `= []` *Request headers.*
- ***param*** `string` version `= "1.1"` *Request protocol version.*

Example:

```php
$request = new \Shieldon\Psr7\Request('GET', 'https://www.example.com');
```

#### getRequestTarget()

- ***return*** `string`

In most cases, this will be the origin-form of the composed URI, unless it is changed by `withRequestTarget` method.

Example:

```php
echo $request->getRequestTarget();
// Outputs: /
```

#### withRequestTarget(`$requestTarget`)

- ***param*** `string` requestTarget `*`
- ***return*** `static`

Example:

```php
$request = $request->withRequestTarget('https://www.example2.com/en/');

echo $request->getRequestTarget();
// Outputs: https://www.example2.com/en/
```

#### getMethod()

- ***return*** `string`

Example:

```php
echo $request->getMethod();
// Outputs: GET
```

#### withMethod(`$method`)

- ***param*** `string` method `*` *Case-sensitive method.*
- ***return*** `static`

Example:

```php
$request = $request->withMethod('POST');
echo $request->getMethod();
// Outputs: POST
```

#### getUri()

- ***return*** `UriInterface`

Example:

```php
echo $request->getUri()->getHost();
// Outputs: www.example.com
```

#### withUri(`$uri`, `$preserveHost`)

- ***param*** `UriInterface` uri `*` *New request URI to use.*
- ***param*** `string` preserveHost `*` *Preserve the original state of the Host header.*
- ***return*** `static`

Example:

```php
$request = new Request('GET', 'https://terryl.in/zh/', '', [], '1.1');

$newRequest = $request->withUri(new Uri('https://play.google.com'));
$newRequest2 = $newRequest->withUri(new Uri('https://www.facebook.com'), true);

echo $newRequest->getUri()->getHost();
// Outputs: play.google.com

echo $newRequest2->getUri()->getHost();
// Outputs: terryl.in
```

---

### ServerRequest

- getServerParams	
- getCookieParams	
- withCookieParams	
- getQueryParams	
- withQueryParams	
- getUploadedFiles	
- withUploadedFiles	
- getParsedBody	
- withParsedBody	
- getAttributes	
- getAttribute	
- withAttribute	
- withoutAttribute

#### __construct

- ***param*** `string` method `= "GET"` *Request HTTP method.*
- ***param*** `string|UriInterface` uri `= ""` *Request URI object URI or URL.*
- ***param*** `string|StreamInterface` body `= ""` *Request body.*
- ***param*** `array` headers `= []` *Request headers.*
- ***param*** `string` version `= "1.1"` *Request protocol version.*
- ***param*** `array` serverParams `= []` *Typically $_SERVER superglobal.*
- ***param*** `array` cookieParams `= []` *Typically $_COOKIE superglobal.*
- ***param*** `array` postParams `= []` *Typically $_POST superglobal.*
- ***param*** `array` getParams `= []` *Typically $_GET superglobal.*
- ***param*** `array` filesParams `= []` *Typically $_FILES superglobal.*

Example:

```php
$serverRequest = new \Shieldon\Psr7\ServerRequest();
```

#### getServerParams()

- ***return*** `array`

Example:

```php
$serverParams = $serverRequests->getServerParams();

print(print_r($serverParams, true));

/* Outputs:

    Array
    (
        [USER] => vagrant
        [HOME] => /home/vagrant
        [HTTP_COOKIE] => PHPSESSID=pca6qln5ab1k7ihthqvuo7rtietguapm
        [HTTP_ACCEPT_LANGUAGE] => en-US,en;q=0.9,zh-TW;q=0.8,zh;q=0.7
        [HTTP_ACCEPT_ENCODING] => gzip, deflate
        [HTTP_ACCEPT] => text/html,application/xhtml+xml,application/xml
        [HTTP_USER_AGENT] => Mozilla/5.0 (Windows NT 10.0; Win64; x64)
        [HTTP_UPGRADE_INSECURE_REQUESTS] => 1
        [HTTP_CACHE_CONTROL] => max-age=0
        [HTTP_CONNECTION] => keep-alive
        [HTTP_HOST] => nantou.welcometw.lo
        [CI_ENV] => development
        [SCRIPT_FILENAME] => /home/terrylin/public/index.php
        [REDIRECT_STATUS] => 200
        [SERVER_NAME] => terryl.lo
        [SERVER_PORT] => 80
        [SERVER_ADDR] => 192.168.33.33
        [REMOTE_PORT] => 64557
        [REMOTE_ADDR] => 192.168.33.1
        [SERVER_SOFTWARE] => nginx/1.14.0
        [GATEWAY_INTERFACE] => CGI/1.1
        [REQUEST_SCHEME] => http
        [SERVER_PROTOCOL] => HTTP/1.1
        [DOCUMENT_ROOT] => /home/terrylin/public
        [DOCUMENT_URI] => /index.php
        [REQUEST_URI] => /
        [SCRIPT_NAME] => /index.php
        [CONTENT_LENGTH] => 
        [CONTENT_TYPE] => 
        [REQUEST_METHOD] => GET
        [QUERY_STRING] => 
        [FCGI_ROLE] => RESPONDER
        [PHP_SELF] => /index.php
        [REQUEST_TIME_FLOAT] => 1591868770.3356
        [REQUEST_TIME] => 1591868770
    )

*/
```

#### getCookieParams()

- ***return*** `array`

Example:

```php
$cookieParams = $serverRequests->getCookieParams();

print(print_r($cookieParams, true));

/* Outputs:

    Array
    (
        [foo] => bar
    )

*/
```

#### getQueryParams()

- ***return*** `array`

Example:

```php
// https://www.example.com/?foo=bar
$queryParams = $serverRequests->getQueryParams();

print(print_r($queryParams, true));

/* Outputs:

    Array
    (
        [foo] => bar
    )

*/
```

#### withQueryParams(`$query`)

- ***param*** `array` query `*` *Array of query string arguments, typically from $_GET.*
- ***return*** `static`

Example:

```php
$serverRequests = $serverRequests->withQueryParams([
    'foo' => 'baz',
    'yes' => 'I do',
]);

$queryParams = $serverRequests->getQueryParams();

print(print_r($queryParams, true));

/* Outputs:

    Array
    (
        [foo] => baz
        [yes] => I do
    )

*/
```

#### getUploadedFiles()

- ***return*** `array`

Example:

```php

$_FILES['avatar'] = [
    'tmp_name' => '/tmp/phpmFLrzD',
    'name' => 'my-avatar.png',
    'type' => 'image/png',
    'error' => 0,
    'size' => 90996,
];

$serverRequest = new \Shieldon\Psr7\ServerRequest(
    'GET', 
    '', 
    '', 
    [], 
    '1.1', 
    [], 
    [], 
    [], 
    [], 
    $_FILES
);

echo $serverRequests->getUploadedFiles()->getClientFilename();
// Outputs: my-avatar.png

echo $serverRequests->getUploadedFiles()->getClientMediaType();
// Outputs: image/png
```

#### getParsedBody()

- ***return*** `null|array|object`

Example:

```php
// Typically, $parsedBody is equal to $_POST superglobal.
$parsedBody = $serverRequest->getParsedBody();
```

#### withParsedBody(`$data`)

- ***param*** `null|array|object` $data `*` *The deserialized body data.*
- ***return*** `static`

Example:

```php
$serverRequest = $serverRequest->withParsedBody(
    [
        'foo' => 'bar',
        'yes' => 'I do'
    ]
);

$parsedBody = $serverRequest->getParsedBody();

echo $parsedBody['yes'];
// Outputs: I do
```

#### getAttributes()

- ***return*** `array`

Example:

```php
$_SESSION['user_name'] = 'terrylin';
$_SESSION['user_role'] = 'admin';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$serverRequest = $serverRequest->
    withAttribute('session', $_SESSION)->
    withAttribute('ip_address', $_SERVER['REMOTE_ADDR']);

$attributes = $serverRequest->getAttributes();

echo $attributes['session']['user_name'];
// Outputs: terrylin

echo $attributes['ip_address'];
// Outputs: 127.0.0.1
```

#### getAttribute(`$name`, `$default`)

- ***param*** `string` name `*` *The attribute name.*
- ***param*** `mixed` default `= null` *Default value to return if the attribute does not exist.*
- ***return*** `mixed`

Example: 

This example extends to the previous one.

```php
$ip = $serverRequest->getAttribute('ip_address');
$session = $serverRequest->getAttribute('session');

// paymentStatus does not exist.
$paymentStatus = $serverRequest->getAttribute('paymentStatus', 'failed');

echo $ip
// Outputs: 127.0.0.1

echo $session['user_role'];
// Outputs: admin

echo $paymentStatus;
// Outputs: failed
```

#### withAttribute(`$name`, `$value`)

- ***param*** `string` name `*` *The attribute name.*
- ***param*** `mixed` value `*` *The value of the attribute.*
- ***return*** `static`

Example:

```php
$serverRequest = $serverRequest->withAttribute('ip_address', '19.89.6.4');
$ip = $serverRequest->getAttribute('ip_address');

echo $ip
// Outputs: 19.89.6.4
```

#### withoutAttribute(`$name`)

- ***param*** `string` name `*` *The attribute name.*
- ***return*** `static`

Example:

```php
$serverRequest = $serverRequest->withoutAttribute('ip_address');
$ip = $serverRequest->getAttribute('ip_address', 'undefined');

echo $ip
// Outputs: undefined
```

---

### Response

- getStatusCode	
- withStatus		
- getReasonPhrase

#### __construct

- ***param*** `int` status `= 200` *Response HTTP status code.*
- ***param*** `array` headers `= []` *Response headers.*
- ***param*** `StreamInterface|string` body `= ""` *Response body.*
- ***param*** `string` version `= "1.1"` *Response protocol version.*
- ***param*** `string` reason `= "OK"` *Reasponse HTTP reason phrase.*

Example:
```php
$response = new \Shieldon\Psr7\Response();
```

#### getStatusCode()

- ***return*** `int`

Example:
```php
$statusCode = $response->getStatusCode();

echo $statusCode
// Outputs: 200
```

#### withStatus(`$code`, `$reasonPhrase`)

- ***param*** `string` code `*` *The 3-digit integer result code to set.*
- ***param*** `string` reasonPhrase `= ""` *The reason phrase to use with the provided status code*
- ***return*** `static`

Example:
```php
$response = $response->withStatus(599, 'Something went wrong.');

echo $response->getStatusCode();
// Outputs: 599

echo $response->getReasonPhrase();
// Outputs: Something went wrong.
```

#### getReasonPhrase()

- ***return*** `string`

Example:

```php
$reasonPhrase = $response->getReasonPhrase();

echo $reasonPhrase
// Outputs: OK
```

---

### Stream

- isWritable
- isReadable
- isSeekable
- close
- detach
- getSize
- tell
- eof
- seek
- rewind
- write
- read
- getContents
- getMetadata
- __toString

#### __construct

- **param** `resource` stream `*` *A valid resource.*

Example:

```php
$stream = new \Shieldon\Psr7\Stream(fopen('php://temp', 'r+'));
```

#### isWritable()

Write data to the stream.

- ***return*** `bool`

Example:

```php
$resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
$stream = new \Shieldon\Psr7\Stream($resource);

if ($stream->isWritable()) {
    echo 'File is writable';
}
// Outputs: File is writable
```

#### isReadable()

Returns whether or not the stream is readable.

- ***return*** `bool`

Example:

```php
$resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
$stream = new \Shieldon\Psr7\Stream($resource);

if ($stream->isReadable()) {
    echo 'File is readable';
}
// Outputs: File is readable
```

#### isSeekable()

Seek to a position in the stream.

- ***return*** `bool`

Example:

```php
$resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
$stream = new \Shieldon\Psr7\Stream($resource);

if ($stream->isSeekable()) {
    echo 'File is seekable';
}
// Outputs: File is seekable
```

#### close()

loses the stream and any underlying resources.

- ***return*** `void`

Example:
```php
$stream = new Stream(fopen('php://temp', 'r+'));
/* ... do something ... */
$stream->close();
```

#### detach()

Separates any underlying resources from the stream. After the stream has been detached, the stream is in an unusable state.

- ***return*** `resource|null`

Example:
```php
$stream = new Stream(fopen('php://temp', 'r+'));
/* ... do something ... */
$legacy = $stream->detach();

if (is_resouce($legacy)) {
    echo 'Resource is detached.';
}
// Outputs: Resource is detached.

$legacy = $stream->detach();

if (is_null($legacy)) {
    echo 'Resource has been null.';
}
// Outputs: Resource has been null.
```

#### getSize()

Get the size of the stream if known.

- ***return*** `int|null`

Example:

```php
$resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
$stream = new \Shieldon\Psr7\Stream($resource);

echo $stream->getSize();
// Outputs: 15166
```

#### tell()

Returns the current position of the file read/write pointer

- ***return*** `int` Position of the file pointer

```php
$resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
$stream = new Stream($resource);

$stream->seek(10);
echo $stream->tell();
// Outputs: 10

$stream->rewind();
echo $stream->tell();
// Outputs: 0

$stream->close();
```

#### eof()

Returns true if the stream is at the end of the stream.

- ***return*** `bool`

```php
$resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
$stream = new Stream($resource);

$stream->seek(10);

if ($stream->eof()) {
    echo 'The position of the file pointer of the stream is at the end.';
} else {
    echo 'Not at the end.';
}
// Outputs: Not at the end.

$stream->seek(15166);

if ($stream->eof()) {
    echo 'The position of the file pointer of the stream is at the end.';
} else {
    echo 'Not at the end.';
}
// Outputs: The position of the file pointer of the stream is at the end.
```

#### seek(`$offset`, `$whence`)

Seek to a position in the stream.

- ***param*** `int` offset `*` *Stream offset.*
- ***param*** `int` whence `= SEEK_SET` *Specifies how the cursor position will be calculated based on the seek offset.*
- ***return*** `void`

Example:

```php
// See eof() example.
```

#### rewind()

Seek to the beginning of the stream.

- ***return*** `void`

Example:

```php
// See tell() example.
```

#### write(`$string`)

- ***param*** `string` string `*` *The string that is to be written.*
- ***return*** `int` *Returns the number of bytes written to the stream.*

Example:

```php
$stream = new Stream(fopen('php://temp', 'r+'));
$stream->write('Foo Bar');

echo $stream->getContents();

// Outputs: Foo Bar
```

#### read(`$length`)

Read data from the stream.

- ***param*** `int` length `*` *Read up to $length bytes from the object and return them.*
- ***return*** `string`

Example:

```php
$stream = new Stream(fopen('php://temp', 'r+'));
$stream->write('Glory to Hong Kong');

echo $stream->read(5);

// Outputs: Glory
```

#### getContents()

Returns the remaining contents in a string

- ***return*** `string`

Example:

```php
$stream = new Stream(fopen('php://temp', 'r+'));
$stream->write('Glory to Hong Kong');

echo $stream->getContents();

// Outputs: Glory to Hong Kong
```

#### getMetadata(`$key`)

Get stream metadata as an associative array or retrieve a specific key.

- ***param*** `string` key `= null` *Specific metadata to retrieve.*
- ***return*** `array|mixed|null`

Example:

```php
$resource = fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+');
$stream = new Stream($resource);
$meta = $stream->getMetadata();

print(print_r($queryParams, true));

/* Outputs:

    Array
    (
        [timed_out] => false
        [blocked] => true
        [eof] => false
        [wrapper_type] => plainfile
        [stream_type] => STDIO
        [mode] => r+
        [unread_bytes] => 0
        [seekable] => true
        [uri] => /home/terrylin/data/psr7/tests/sample/shieldon_logo.png
    )
*/

echo $stream->getMetadata('mode')
// Outputs: r+
```

#### __toString()

Reads all data from the stream into a string, from the beginning to end.

- ***return*** `string`

Example:

```php
$stream = new Stream(fopen('php://temp', 'r+'));
$stream->write('Foo Bar');

echo $stream;
// Outputs: Foo Bar
```

---

### UploadedFile

- *__construct*
- getStream
- moveTo	
- getSize	
- getError	
- getClientFilename	
- getClientMediaType

#### __construct

- ***param*** `string|StreamInterface` source `*` *The full path of a file or stream.*
- ***param*** `string|null` name `= null` *The file name.*
- ***param*** `string|null` type `= null` *The file media type.*
- ***param*** `int|null` size `= null` *The file size in bytes.*
- ***param*** `int` error `= 0` *The status code of the upload.*
- ***param*** `string|null` sapi `= null` *Only assign for unit testing purpose.*

Example:
```php
$uploadedFile = new \Shieldon\Psr7\UploadedFile(
    '/tmp/php200A.tmp', // source
    'example1.jpg',     // name
    'image/jpeg',       // type
    100000,             // size
    0                   // error
);
```

#### getStream()

Retrieve a stream representing the uploaded file.

- ***return*** `StreamInterface`

Example:

```php
$stream = new Stream(fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+'));
$uploadedFile = new UploadedFile($stream);

$stream2 = $uploadedFile->getStream();

echo $stream2->getMetadata('mode');
// Outputs: r+
```

#### moveTo($targetPath)

Move the uploaded file to a new location.

- ***param*** `string` targetPath `*` *Path to which to move the uploaded file.*

```php
$stream = new Stream(
    fopen(BOOTSTRAP_DIR . '/sample/shieldon_logo.png', 'r+')
);

$uploadedFile = new UploadedFile($stream);

$uploadedFile->moveTo('/home/terrylin/public/image_cache/shieldon_logo_png');

if (
    file_exists('/home/terrylin/public/image_cache/shieldon_logo_png') &&
    ! file_exists(BOOTSTRAP_DIR . '/sample/shieldon_logo.png')
) {
    echo 'File has been moved to the new place.';
} else {
    echo 'Cannot move file.';
}
// Outputs: File has been moved to the new place.
```

#### getSize()

Retrieve the file size.

- ***return*** `int|null`

Example:

```php
$uploadedFile = new \Shieldon\Psr7\UploadedFile(
    '/tmp/php200A.tmp',
    'example1.jpg',
    'image/jpeg',
    100000,
    0
);

echo $uploadedFile->getSize();
// Outputs: 100000
```

#### getError()

Retrieve the error associated with the uploaded file.

- ***return*** `int`

Example:

```php
$uploadedFile = new \Shieldon\Psr7\UploadedFile(
    '/tmp/php200A.tmp',
    'example1.jpg',
    'image/jpeg',
    100000,
    0
);

$uploadedFile->getError();
// Outputs: 0
```

#### getClientFilename()

Retrieve the filename sent by the client.

- ***return*** `string|null`

Example:

```php
$uploadedFile = new \Shieldon\Psr7\UploadedFile(
    '/tmp/php200A.tmp',
    'example1.jpg',
    'image/jpeg',
    100000,
    0
);

$uploadedFile->getClientFilename();
// Outputs: example1.jpg
```

#### getClientMediaType()

Retrieve the media type sent by the client.

- ***return*** `string|null`

Example:

```php
$uploadedFile = new \Shieldon\Psr7\UploadedFile(
    '/tmp/php200A.tmp',
    'example1.jpg',
    'image/jpeg',
    100000,
    0
);

$uploadedFile->getClientMediaType();
// Outputs: image/jpeg
```

---

### Uri

- getScheme	
- getAuthority	
- getUserInfo	
- getHost	
- getPort	
- getPath	
- getQuery	
- getFragment	
- withScheme	
- withUserInfo	
- withHost	
- withPort	
- withPath	
- withQuery	
- withFragment	
- __toString

#### __construct

- ***param*** `string` uri `= ""` *The URI.*

Example:
```php
$uri = new \Shieldon\Psr7\Uri('https://www.example.com');
```

#### getScheme()

- ***return*** `string`

Example:

```php
$uri = new \Shieldon\Psr7\Uri(
    'https://www.example.com'
);
echo $uri->getScheme();
// Outputs: https
```

#### getAuthority()

- ***return*** `string`

Example:

```php
$uri = new \Shieldon\Psr7\Uri(
    'https://terry:1234@example.com:8888/phpMyAdmin/'
);
echo $uri->getAuthority();
// Outputs: terry:1234@example.com:8888
```

#### getUserInfo()

- ***return*** `string`

Example:

```php
$uri = new \Shieldon\Psr7\Uri(
    'https://terry:1234@example.com:8888/phpMyAdmin/'
);
echo $uri->getUserInfo();
// Outputs: terry:1234
```

#### getHost()

- ***return*** `string`

Example:

```php
$uri = new \Shieldon\Psr7\Uri(
    'https://terry:1234@example.com:8888/phpMyAdmin/'
);
echo $uri->getHost();
// Outputs: example.com
```

#### getPort()

- ***return*** `int|null`

Example:

```php
$uri = new \Shieldon\Psr7\Uri(
    'https://terry:1234@example.com:8888/phpMyAdmin/'
);
echo $uri->getPort();
// Outputs: 8888
```

#### getPath

- ***return*** `string`

Example:

```php
$uri = new \Shieldon\Psr7\Uri(
    'https://example.com/post/?p=113&foo=bar#yes-i-do'
);
echo $uri->getPath();
// Outputs: /post/
```

#### getQuery

- ***return*** `string`

Example:

```php
$uri = new \Shieldon\Psr7\Uri(
    'https://example.com/post/?p=113&foo=bar#yes-i-do'
);
echo $uri->getQuery();
// Outputs: p=113&foo=bar
```

#### getFragment

- ***return*** `string`

Example:

```php
$uri = new \Shieldon\Psr7\Uri(
    'https://example.com/post/?p=113&foo=bar#yes-i-do'
);
echo $uri->getFragment();
// Outputs: yes-i-do
```

#### withScheme(`$scheme`)

- ***param*** `string` scheme `*` *The scheme to use with the new instance.*
- ***return*** `static`

Example:

```php
echo $uri->getScheme();
// Outputs: https

$url = $uri->withScheme('http');
echo $uri->getScheme();
// Outputs: http
```

#### withUserInfo(`$user`, `$password`)

- ***param*** `string` user `*` *The user name to use for authority.*
- ***param*** `string|null` password `= null` *The password associated with $user.*

- ***return*** `static`

Example:

```php
echo $uri->getUserInfo();
// Outputs: terry:1234

$url = $uri->withUserInfo('jack', '5678');
echo $uri->getUserInfo();
// Outputs: jack:5678
```

#### withHost(`$host`)

- ***param*** `string` host `*` *The hostname to use with the new instance.*
- ***return*** `static`

Example:

```php
echo $uri->getHost();
// Outputs: example.com

$url = $uri->withHost('terryl.in');
echo $uri->getHost();
// Outputs: terryl.in
```

#### withPort(`$port`)

- ***param*** `int|null` port `*` *The port to use with the new instance; a null value removes the port information.*
- ***return*** `static`

Example:

```php
echo $uri->getPort();
// Outputs: 8888

$uri = $uri->withPort(443);
echo $uri->getPort();
// Outputs: 443

$uri = $uri->withPort(null);
echo $uri->getPort();
// Outputs:
```

#### withPath(`$path`)

- ***param*** `string` path `*` *The path to use with the new instance.*
- ***return*** `static`

Example:

```php
echo $uri->getPath();
// Outputs: /post/

$uri = $uri->withPath('/new-path');
echo $uri->getPath();
// Outputs: /new-path
```

#### withQuery(`$query`)

- ***param*** `string` query `*` *The query string to use with the new instance.*
- ***return*** `static`

Example:

```php
echo $uri->getQuery();
// Outputs: p=113&foo=bar

$uri = $uri->witQuery('p=120&foo=baz');
echo $uri->getQuery();
// Outputs: p=120&foo=baz
```

#### withFragment(`$fragment`)

- ***param*** `string` fragment `*` *The fragment to use with the new instance.*
- ***return*** `static`

Example:

```php
echo $uri->getFragment();
// Outputs: yes-i-do

$uri = $uri->withFragment('no-i-cant');
echo $uri->getFragment();
// Outputs: no-i-cant
```

#### __toString

- ***return*** `string`

Example:

```php
$uri = new Uri('http://example.com:8888/demo/#section-1');
echo $uri;
// Outputs: http://example.com:8888/demo/#section-1
```

---

## PSR-15 Middleware

- RequestHandler
- Middleware

### RequestHandler

- add
- handle

#### __construct(`$fallbackHandler`)

- ***param*** `RequestHandlerInterface` fallbackHandler `*` *The request handler.*

Example:

```php
$finalHandler = new FinalHandler();

$app = new RequestHandler($finalHandler);
$app->add(new ApiMiddleware());
$app->add(new StringMiddleware());

$response = $app->handle(ServerRequestFactory::fromGlobal());
```

#### add(`$middleware`)

- ***param*** `MiddlewareInterface` middleware `*` *The middleware.*
- ***return*** `void`

Example:

```php
$app->add(new ApiMiddleware());
```

#### handle(`request`)

- ***param*** `ServerRequestInterface` request `*` *The server request.*
- ***return*** `ResponseInterface`

Example:

```php
$response = $app->handle(ServerRequestFactory::fromGlobal());
```

### Middleware

- process

#### __construct

None

#### process(`$request`, `$handler`)

- ***param*** `ServerRequestInterface` request `*` *The server request.*
- ***param*** `RequestHandlerInterface` handler `*` *The request handler.*
- ***return*** `ResponseInterface`

Example:

```php
class ApiMiddleware extends Middleware
{
    public function process(ServerRequestInterface  $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');
        $key = $request->getHeaderLine('key');
        $secret = $request->getHeaderLine('secret');

        if ($contentType !== 'application/json') {
            return (new Response)->withStatus(406, 'Content type is not accepted.');
        }

        if ($key !== '23492834234') {
            return (new Response)->withStatus(401, 'API key is invalid.');
        }

        if ($secret !== '1a163782ee166156294d173fcf8b8e87') {
            return (new Response)->withStatus(401, 'API secret is invalid.');
        }

        return $handler->handle($request);
    }
}
```

--- 

## Author

Shieldon HTTP message library (PSR-7 implementation) is brought to you by [Terry L.](https://terryl.in) from Taiwan.

## License

Shieldon HTTP message library is an open-sourced software licensed under the **MIT** license.