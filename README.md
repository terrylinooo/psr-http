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

#### createRequest

- ***param*** `string ` $method
*The HTTP method associated with the request.*
- ***param*** `UriInterface|string` $uri
*The URI associated with the request.*
- ***return*** `RequestInterface`


Example:
```php
use Shieldon\Psr7\Factory\RequestFactory;

$requestFactory = new RequestFactory();
$request = $requestFactory->createRequest('GET', 'https://www.google.com');
```

### ServerRequestFactory

#### __construct

- **param** `bool` $autoDetermine `= false`
*Determine HTTP method and URI automatically.*

```php
use Shieldon\Psr7\Factory\ServerRequestFactory;

$serverRequestFactory = new ServerRequestFactory(true);
```

PSR-17 document says, *In particular, no attempt is made to determine the HTTP method or URI, which must be provided explicitly.*
 
I think that HTTP method and URI can be given by superglobal in SAPI enviornment, since it is a server-side request. This is an option to allow you automatically determine the HTTP method and URI when `$method` and `$uri` are empty.

#### createServerRequest

- ***param*** `string` $method
*The HTTP method associated with the request.*
- ***param*** `UriInterface|string` $uri
*The URI associated with the request.*
- ***param*** `array` $serverParams `= []`
*An array of Server API (SAPI) parameters with which to seed the generated request instance.*
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

- ***param*** `int` $code `= 200`
*The HTTP status code.*
- ***param*** `string` $reasonPhrase `= ''`
*The reason phrase to associate with the status code.*
- ***return*** `ResponseInterface`

Example:
```php
use Shieldon\Psr7\Factory\ResponseFactory;

$responseFactory = new ResponseFactory();
$response = $responseFactory->createResponse(200, 'OK');
```

## PSR-7

- Message
- Request
- ServerRequest
- Response
- Stream
- UploadedFile
- Uri
