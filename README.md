# PSR 7, 15, 17 Implementation and Examples

![build](https://github.com/terrylinooo/psr-http/workflows/build/badge.svg) [![codecov](https://img.shields.io/codecov/c/github/terrylinooo/psr-http.svg)](https://codecov.io/gh/terrylinooo/psr-http) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/terrylinooo/psr-http/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/terrylinooo/psr-http/?branch=master) [![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

This library is a PSR HTTP implementation created for [Shieldon firewall 2](https://github.com/terrylinooo/shieldon) , following up the PSR (PHP Standard Recommendation) documents by one hundred percent.

- **PSR-7** (HTTP Message Interfaces)
- **PSR-15** (HTTP Server Request Handlers)
- **PSR-17** (HTTP Factories)

### Test Status

Shiledon PSR-HTTP library is strictly tested by unit tests contain almost all conditions that might be happened, if you find any bug or something that can improve this library, please let me know.

| Test suite | Status |
| --- | --- |
| Repository built-in tests | [![Build Status](https://travis-ci.org/terrylinooo/psr-http.svg?branch=master)](https://travis-ci.org/terrylinooo/psr-http) 
| [PSR-7 integration tests](https://github.com/terrylinooo/psr7-integration-tests) | [![Build Status](https://travis-ci.org/terrylinooo/psr7-integration-tests.svg?branch=shieldon-psr-http)](https://travis-ci.org/terrylinooo/psr7-integration-tests) 


You can use it on any framework which is compatible with those PSRs.

## Install

```php
composer require shieldon/psr-http
```

## Test
```bash
composer install
composer test
```

## Quick Start

The simplest way to start implementing PSR-7 on your PHP applications, let's check out the examples below.

*Create a server request.* 

```php

$serverRequest = ServerRequestFactory::fromGlobal();
```

*Create a  request.*

```php
$request = RequestFactory::fromNew();
```

*Create a server response*

```php
$response = ResponseFactory::fromNew();
```


*Create a URI.*

```php
// Create a URI contains visitor's information.

/**
 *  Assume a visior is viewing your website page, 
 *  for example, https://yoursite/en/post1.html
 */
$uri = UriFactory::fromGlobal();

echo $uri->getPath();
// Outputs: /en/post1.html

// Create a URI just a new instance.
$uri = UriFactory::fromNew();

echo $uri->getPath();
// Outputs: 
```

*Create a stream instance.*

```php
// Create a stream just a new instance.
$stream = StreamFactory::fromNew();
```

*Create an array with UploadedFile structure.*

```php
// Let's see the following example, 
// assume we have a superglobal $_FILES looks like this.
$_FILES = [
    'foo' => [
        'name' => 'example1.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => '/tmp/php200A.tmp',
        'error' => 0,
        'size' => 100000,
    ]
];

$uploadFileArr = UploadedFileFactory::fromGlobal();

echo $uploadFileArr['foo']->getClientFilename();
// Outputs: example1.jpg
```

## Table of Contents
- ### **PSR-17**: *HTTP Factories*
    - #### [RequestFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-RequestFactory-Example)
        - [createRequest](https://github.com/terrylinooo/psr-http/wiki/RequestFactory:-createRequest-Example)
        - [::fromNew](https://github.com/terrylinooo/psr-http/wiki/RequestFactory:-fromNew-Example)  `(Non-PSR)`
    - #### [ServerRequestFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-ServerRequestFactory-Example)
        - [createServerRequest](https://github.com/terrylinooo/psr-http/wiki/ServerRequestFactory:-createServerRequest-Example)
        - [::fromGlobal](https://github.com/terrylinooo/psr-http/wiki/ServerRequestFactory:-fromGlobal-Example) `(Non-PSR)`
    - #### [ResponseFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-ResponseFactory-Example)
        - [createResponse](https://github.com/terrylinooo/psr-http/wiki/ResponseFactory:-createResponse-Example)
        - [::fromNew](https://github.com/terrylinooo/psr-http/wiki/ResponseFactory:-fromNew-Example)  `(Non-PSR)`
    - #### [StreamFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-StreamFactory-Example)
        - [createStream](https://github.com/terrylinooo/psr-http/wiki/StreamFactory:-createStream-Example)
        - [createStreamFromFile](https://github.com/terrylinooo/psr-http/wiki/StreamFactory:-createStreamFromFile-Example)
        - [createStreamFromResource](https://github.com/terrylinooo/psr-http/wiki/StreamFactory:-createStreamFromResource-Example)
        - [::fromNew](https://github.com/terrylinooo/psr-http/wiki/UploadedFileFactory:-fromNew-Example)  `(Non-PSR)`
    - #### [UploadedFileFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-UploadedFileFactory-Example)
        - [createUploadedFile](https://github.com/terrylinooo/psr-http/wiki/UploadedFileFactory:-createUploadedFile-Example)
        - [::fromGlobal](https://github.com/terrylinooo/psr-http/wiki/UploadedFileFactory:-fromGlobal-Example)  `(Non-PSR)`
    - #### [UriFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-UriFactory-Example)
        - [createUri](https://github.com/terrylinooo/psr-http/wiki/UriFactory:-createUri-Example)
        - [::fromGlobal](https://github.com/terrylinooo/psr-http/wiki/UriFactory:-fromGlobal-Example)  `(Non-PSR)`
        - [::fromNew](https://github.com/terrylinooo/psr-http/wiki/UriFactory:-fromNew-Example)  `(Non-PSR)`
- ### **PSR-7**: *HTTP Message Interfaces*
    - #### [Message](https://github.com/terrylinooo/psr-http/wiki/PSR-7:-Message-Example)
        - [getProtocolVersion](https://github.com/terrylinooo/psr-http/wiki/Message:-getProtocolVersion-Example)
        - [withProtocolVersion](https://github.com/terrylinooo/psr-http/wiki/Message:-withProtocolVersion-Example)
        - [setHeaders](https://github.com/terrylinooo/psr-http/wiki/Message:-setHeaders-Example) `(Non-PSR)`
        - [getHeaders](https://github.com/terrylinooo/psr-http/wiki/Message:-getHeaders-Example)
        - [hasHeader](https://github.com/terrylinooo/psr-http/wiki/Message:-hasHeader-Example)
        - [getHeader](https://github.com/terrylinooo/psr-http/wiki/Message:-getHeader-Example)
        - [getHeaderLine](https://github.com/terrylinooo/psr-http/wiki/Message:-getHeaderLine-Example)
        - [withHeader](https://github.com/terrylinooo/psr-http/wiki/Message:-withHeader-Example)
        - [withAddedHeader](https://github.com/terrylinooo/psr-http/wiki/Message:-withAddedHeader-Example)
        - [withoutHeader](https://github.com/terrylinooo/psr-http/wiki/Message:-withoutHeader-Example)
        - [getBody](https://github.com/terrylinooo/psr-http/wiki/Message:-getBody-Example)
        - [withBody](https://github.com/terrylinooo/psr-http/wiki/Message:-withBody-Example)
    - #### [Request](https://github.com/terrylinooo/psr-http/wiki/PSR-7:-Request-Example) *(externds Message)*
        - [__construct](https://github.com/terrylinooo/psr-http/wiki/Request:-__construct-Example) `(Non-PSR)`
        - [getRequestTarget](https://github.com/terrylinooo/psr-http/wiki/Request:-getRequestTarget-Example)
        - [withRequestTarget](https://github.com/terrylinooo/psr-http/wiki/Request:-withRequestTarget-Example)
        - [getMethod](https://github.com/terrylinooo/psr-http/wiki/Request:-getMethod-Example)
        - [withMethod](https://github.com/terrylinooo/psr-http/wiki/Request:-withMethod-Example)
        - [getUri](https://github.com/terrylinooo/psr-http/wiki/Request:-getUri-Example)
        - [withUri](https://github.com/terrylinooo/psr-http/wiki/Request:-withUri-Example)
    - #### [ServerRequest](https://github.com/terrylinooo/psr-http/wiki/PSR-7:-ServerRequest-Example) *(externds Request)*
        - [__construct](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-__construct-Example) `(Non-PSR)`
        - [getServerParams](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-getServerParams-Example)
        - [getCookieParams](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-getCookieParams-Example)
        - [withCookieParams](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-withCookieParams-Example)
        - [getQueryParams](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-getQueryParams-Example)	
        - [withQueryParams](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-withQueryParams-Example)	
        - [getUploadedFiles](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-getUploadedFiles-Example)	
        - [withUploadedFiles](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-withUploadedFiles-Example)	
        - [getParsedBody](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-getParsedBody-Example) (See explanation below)
        - [withParsedBody](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-withParsedBody-Example)	
        - [getAttributes](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-getAttributes-Example)	
        - [getAttribute](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-getAttribute-Example)
        - [withAttribute](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-withAttribute-Example)	
        - [withoutAttribute](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-withoutAttribute-Example)
    - #### [Response](https://github.com/terrylinooo/psr-http/wiki/PSR-7:-Response-Example) *(externds Message)*
        - [__construct](https://github.com/terrylinooo/psr-http/wiki/Request:-__construct-Example) `(Non-PSR)`
        - [getStatusCode](https://github.com/terrylinooo/psr-http/wiki/Request:-getStatusCode-Example)
        - [withStatus](https://github.com/terrylinooo/psr-http/wiki/Request:-withStatus-Example)	
        - [getReasonPhrase](https://github.com/terrylinooo/psr-http/wiki/Request:-getReasonPhrase-Example)
    - #### [Stream](https://github.com/terrylinooo/psr-http/wiki/PSR-7:-Stream-Example) 
        - [__construct](https://github.com/terrylinooo/psr-http/wiki/Stream:-__construct-Example) `(Non-PSR)`
        - [isWritable](https://github.com/terrylinooo/psr-http/wiki/Stream:-isWritable-Example)
        - [isReadable](https://github.com/terrylinooo/psr-http/wiki/Stream:-isReadable-Example)
        - [isSeekable](https://github.com/terrylinooo/psr-http/wiki/Stream:-isSeekable-Example)
        - [close](https://github.com/terrylinooo/psr-http/wiki/Stream:-close-Example)
        - [detach](https://github.com/terrylinooo/psr-http/wiki/Stream:-detach-Example)
        - [getSize](https://github.com/terrylinooo/psr-http/wiki/Stream:-getSize-Example)
        - [tell](https://github.com/terrylinooo/psr-http/wiki/Stream:-tell-Example)
        - [eof](https://github.com/terrylinooo/psr-http/wiki/Stream:-eof-Example)
        - [seek](https://github.com/terrylinooo/psr-http/wiki/Stream:-seek-Example)
        - [rewind](https://github.com/terrylinooo/psr-http/wiki/Stream:-rewind-Example)
        - [write](https://github.com/terrylinooo/psr-http/wiki/Stream:-write-Example)
        - [read](https://github.com/terrylinooo/psr-http/wiki/Stream:-read-Example)
        - [getContents](https://github.com/terrylinooo/psr-http/wiki/Stream:-getContents-Example)
        - [getMetadata](https://github.com/terrylinooo/psr-http/wiki/Stream:-getMetadata-Example)
        - [__toString](https://github.com/terrylinooo/psr-http/wiki/Stream:-__toString-Example)
    - #### [UploadedFile](https://github.com/terrylinooo/psr-http/wiki/PSR-7:-UploadedFile-Example)
        - [__construct](https://github.com/terrylinooo/psr-http/wiki/UploadedFile:-__construct-Example) `(Non-PSR)`
        - [getStream](https://github.com/terrylinooo/psr-http/wiki/UploadedFile:-getStream-Example)
        - [moveTo](https://github.com/terrylinooo/psr-http/wiki/UploadedFile:-moveTo-Example)
        - [getSize](https://github.com/terrylinooo/psr-http/wiki/UploadedFile:-getSize-Example)
        - [getError](https://github.com/terrylinooo/psr-http/wiki/UploadedFile:-getError-Example)
        - [getClientFilename](https://github.com/terrylinooo/psr-http/wiki/UploadedFile:-getClientFilename-Example)
        - [getClientMediaType](https://github.com/terrylinooo/psr-http/wiki/UploadedFile:-getClientMediaType-Example)
        - [getErrorMessage](https://github.com/terrylinooo/psr-http/wiki/UploadedFile:-getErrorMessage-Example) `(Non-PSR)`
    - #### [Uri](https://github.com/terrylinooo/psr-http/wiki/PSR-7:-Uri-Example)
        - [__construct](https://github.com/terrylinooo/psr-http/wiki/Uri:-__construct-Example) `(Non-PSR)`
        - [getScheme](https://github.com/terrylinooo/psr-http/wiki/Uri:-getScheme-Example) 
        - [getAuthority](https://github.com/terrylinooo/psr-http/wiki/Uri:-getAuthority-Example) 
        - [getUserInfo](https://github.com/terrylinooo/psr-http/wiki/Uri:-getUserInfo-Example) 
        - [getHost](https://github.com/terrylinooo/psr-http/wiki/Uri:-getHost-Example) 
        - [getPort](https://github.com/terrylinooo/psr-http/wiki/Uri:-getPort-Example) 
        - [getPath](https://github.com/terrylinooo/psr-http/wiki/Uri:-getPath-Example) 
        - [getQuery](https://github.com/terrylinooo/psr-http/wiki/Uri:-getQuery-Example) 
        - [getFragment](https://github.com/terrylinooo/psr-http/wiki/Uri:-getFragment-Example) 
        - [withScheme](https://github.com/terrylinooo/psr-http/wiki/Uri:-withScheme-Example) 
        - [withUserInfo](https://github.com/terrylinooo/psr-http/wiki/Uri:-withUserInfo-Example) 
        - [withHost](https://github.com/terrylinooo/psr-http/wiki/Uri:-withHost-Example) 
        - [withPort](https://github.com/terrylinooo/psr-http/wiki/Uri:-withPort-Example) 
        - [withPath](https://github.com/terrylinooo/psr-http/wiki/Uri:-withPath-Example) 
        - [withQuery](https://github.com/terrylinooo/psr-http/wiki/Uri:-withQuery-Example) 
        - [withFragment](https://github.com/terrylinooo/psr-http/wiki/Uri:-withFragment-Example) 
        - [__toString](https://github.com/terrylinooo/psr-http/wiki/Uri:-__toString-Example) 
- ### **PSR-15**: *HTTP Server Request Handlers*
    - #### [RequestHandler](https://github.com/terrylinooo/psr-http/wiki/PSR-15:-RequestHandler-Example)
        - [__construct](https://github.com/terrylinooo/psr-http/wiki/RequestHandler:-__construct-Example) `(Non-PSR)`
        - [add](https://github.com/terrylinooo/psr-http/wiki/RequestHandler:-add-Example)
        - [handle](https://github.com/terrylinooo/psr-http/wiki/RequestHandler:-handle-Example)
    - #### [Middleware](https://github.com/terrylinooo/psr-http/wiki/PSR-15:-Middleware-Example)
        - [process](https://github.com/terrylinooo/psr-http/wiki/Middleware:-process-Example)


If you are looking for combined examples, see [unit testing](https://github.com/terrylinooo/psr-http/tree/master/tests).

### The Behavior of Handling Request Body

Shieldon PSR-HTTP is ready for RESTful, the following content explains how PRS-HTTP deals with the request body.

The `getParsedBody` method will return:

- **(A)** An array of the superglobal *$_POST* if the request request method is `POST` and the Content-Type is one of the following types:
    - `multipart/form-data`
    - `application/x-www-form-urlencode`
    

- **(B)** A JSON object if the request fit to the following conditions.
    - The request Content-Type is `application/json`
    - The request body is a valid *JSON-formatted* string.
    - The request method is not `GET`.

- **(C)** An array parsed from HTTP build query:
    - The condition is neither A or B.
    - The request method is not `GET`.

- **(D)** `null` if the condition is none of above.

#### Summary

| Condition| Method | Content-type | Parsed-body |
| --- | --- | --- | --- |
| A | POST | multipart/form-data<br />application/x-www-form-urlencode | array |
| B | ALL excepts GET | application/json | object  |
| C | ALL excepts GET | All excepts A or B | array |
| D | - | - | null |

Hope this helps.

--- 

## Author

Shieldon PSR HTTP library is brought to you by [Terry L.](https://terryl.in) from Taiwan.

## License

MIT

## References

- [PSR-7](https://www.php-fig.org/psr/psr-7/) (HTTP Message Interfaces)
- [PSR-15](https://www.php-fig.org/psr/psr-15/) (HTTP Server Request Handlers)
- [PSR-17](https://www.php-fig.org/psr/psr-17/) (HTTP Factories)
