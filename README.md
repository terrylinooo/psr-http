# PHP PSR-7 Tutorial, Implementation and Examples

[![Build Status](https://travis-ci.org/terrylinooo/psr7.svg?branch=master)](https://travis-ci.org/terrylinooo/psr7) [![codecov](https://img.shields.io/codecov/c/github/terrylinooo/psr7.svg)](https://codecov.io/gh/terrylinooo/psr7) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

This library is a PSR-7 implementation used by [Shieldon](https://github.com/terrylinooo/shieldon) firewall 2 version, following up the PSR-7 [HTTP message interfaces](https://www.php-fig.org/psr/psr-7/) document. You can use it on any framework which is compatible with the PSR-7 standard.

## Install

```php
composer require shieldon/psr7
```

The Shieldon PSR-7 implementation requires at least PHP 7.1 to run.
The usages of every method can be found in the [unit tests](https://github.com/terrylinooo/psr7/tree/master/tests/Psr7).

## Factories

- RequestFactory
- ServerRequestFactory
- ResponseFactory
- StreamFactory
- UploadedFileFactory
- UriFactory

Here are some examples that show you the way creating PSR-7 instances from PSR-17 HTTP factories.

### RequestFactory

#### __construct

RequestFactory does not have a Constructor.

#### createRequest(`$method`, `$uri`)

- ***param*** `string ` method `*` *The HTTP method associated with the request.*
- ***param*** `UriInterface|string` uri `*` *The URI associated with the request.*
- ***return*** `RequestInterface`


Example:
```php
use Shieldon\Psr7\Factory\RequestFactory;

$requestFactory = new RequestFactory();
$request = $requestFactory->createRequest('GET', 'https://www.google.com');
```

### ServerRequestFactory

#### __construct(`$autoDetermine`)

- **param** `bool` autoDetermine `= false` *Determine HTTP method and URI automatically.*

Example:

```php
use Shieldon\Psr7\Factory\ServerRequestFactory;

$serverRequestFactory = new ServerRequestFactory(true);
```

PSR-17 document says, *In particular, no attempt is made to determine the HTTP method or URI, which must be provided explicitly.*
 
I think that HTTP method and URI can be given by superglobal in SAPI enviornment, since it is a server-side request. This is an option to allow you automatically determine the HTTP method and URI when `$method` and `$uri` are empty.

#### createServerRequest(`$method`, `$uri`, `$serverParams`)

- ***param*** `string` method `*`*The HTTP method associated with the request.*
- ***param*** `UriInterface|string` uri `*`*The URI associated with the request.*
- ***param*** `array` serverParams `= []` *An array of Server API (SAPI) parameters with which to seed the generated request instance.*
- ***return*** `ServerRequestInterface`

Examples:

Determine HTTP method and URI automatically.

```php
$serverRequestFactory = new ServerRequestFactory(true);
$serverRequest = $serverRequestFactory->createServerRequest('', '');
```

Or, the HTTP method and URI must be provided explicitly.

```php
$serverRequestFactory = new ServerRequestFactory();

$method = 'GET';
$url = 'https://www.yourwebsite.com/current-page/';

$serverRequest = $serverRequestFactory->createServerRequest($method, $uri);
```

### ResponseFactory

#### __construct

ResponseFactory does not have a Constructor.

#### createResponse

- ***param*** `int` code `= 200` *The HTTP status code.*
- ***param*** `string` reasonPhrase `= ''` *The reason phrase to associate with the status code.*
- ***return*** `ResponseInterface`

Example:
```php
use Shieldon\Psr7\Factory\ResponseFactory;

$responseFactory = new ResponseFactory();
$response = $responseFactory->createResponse(200, 'OK');
```

## PSR-7

- Message
- Request *(externds Message)*
- ServerRequest *(externds Request)*
- Response
- Stream
- UploadedFile
- Uri

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

ResponseFactory does not have a Constructor.

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
- ***param*** `mixed` filesParams `= null` *Default value to return if the attribute does not exist.*
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
// undefined
```

---

### Response

#### __construct

- ***param*** `int` status `= 200` *Response HTTP status code.*
- ***param*** `array` headers `= []` *Response headers.*
- ***param*** `StreamInterface|string` body `= ""` *Response body.*
- ***param*** `string` version `= "1.1"` *Response protocol version.*
 ***param*** `string` reason `= "OK"` *Reasponse HTTP reason phrase.*

Example:
```php
$response = new \Shieldon\Psr7\Response();
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

- **param** `string|StreamInterface` source `*` *The full path of a file or stream.*
- **param** `string|null` name `= null` *The file name.*
- **param** `string|null` type `= null` *The file media type.*
- **param** `int|null` size `= null` *The file size in bytes.*
- **param** `int` error `= 0` *The status code of the upload.*
- **param** `string|null` sapi `= null` *Only assign for unit testing purpose.*

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

- **param** `string` uri `= ""` *The URI.*

Example:
```php
$uri = new \Shieldon\Psr7\Uri('https://www.example.com');
```