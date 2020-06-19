# PSR 7, 15, 17 Implementation and Examples

[![Build Status](https://travis-ci.org/terrylinooo/psr-http.svg?branch=master)](https://travis-ci.org/terrylinooo/psr-http) [![codecov](https://img.shields.io/codecov/c/github/terrylinooo/psr-http.svg)](https://codecov.io/gh/terrylinooo/psr-http) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

This library is a PSR HTTP implementation with detailed examples, following up the PSR (PHP Standard Recommendation) documents by one hundred percent.

- [PSR-7](https://www.php-fig.org/psr/psr-7/) (HTTP Message Interfaces)
- [PSR-15](https://www.php-fig.org/psr/psr-15/) (HTTP Server Request Handlers)
- [PSR-17](https://www.php-fig.org/psr/psr-17/) (HTTP Factories)

You can use it on any framework which is compatible with those PSRs.

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
- ### **PSR-17**: *HTTP Factories*
    - #### [RequestFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-RequestFactory-Example)
        - [createRequest](https://github.com/terrylinooo/psr-http/wiki/RequestFactory:-createRequest-Example)
    - #### [ServerRequestFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-ServerRequestFactory-Example)
        - [createServerRequest](https://github.com/terrylinooo/psr-http/wiki/ServerRequestFactory:-createServerRequest-Example)
        - [::fromGlobal](https://github.com/terrylinooo/psr-http/wiki/ServerRequestFactory:-fromGlobal-Example) `(Non-PSR)`
    - #### [ResponseFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-ResponseFactory-Example)
        - [createResponse](https://github.com/terrylinooo/psr-http/wiki/ResponseFactory:-createResponse-Example)
    - #### [StreamFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-StreamFactory-Example)
        - [createStream](https://github.com/terrylinooo/psr-http/wiki/StreamFactory:-createStream-Example)
        - [createStreamFromFile](https://github.com/terrylinooo/psr-http/wiki/StreamFactory:-createStreamFromFile-Example)
        - [createStreamFromResource](https://github.com/terrylinooo/psr-http/wiki/StreamFactory:-createStreamFromResource-Example)
    - #### [UploadedFileFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-UploadedFileFactory-Example)
        - [createUploadedFile](https://github.com/terrylinooo/psr-http/wiki/UploadedFileFactory:-createUploadedFile-Example)
    - #### [UriFactory](https://github.com/terrylinooo/psr-http/wiki/PSR-17:-UriFactory-Example)
        - [createUri](https://github.com/terrylinooo/psr-http/wiki/UriFactory:-createUri-Example)
        - [::fromGlobal](https://github.com/terrylinooo/psr-http/wiki/UriFactory:-fromGlobal-Example)  `(Non-PSR)`
- ### **PSR-7**: *HTTP Message Interfaces*
    - #### [Message](https://github.com/terrylinooo/psr-http/wiki/PSR-7:-Message-Example)
        - [getProtocolVersion](https://github.com/terrylinooo/psr-http/wiki/Message:-getProtocolVersion-Example)
        - [withProtocolVersion](https://github.com/terrylinooo/psr-http/wiki/Message:-withProtocolVersion-Example)
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
        - [getParsedBody](https://github.com/terrylinooo/psr-http/wiki/ServerRequest:-getParsedBody-Example)
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

--- 

## Author

Shieldon PSR HTTP library is brought to you by [Terry L.](https://terryl.in) from Taiwan.

## License

MIT
