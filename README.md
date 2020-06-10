# PHP PSR-7 Tutorial, implementation and Examples

[![Build Status](https://travis-ci.org/terrylinooo/psr7.svg?branch=master)](https://travis-ci.org/terrylinooo/psr7) [![codecov](https://img.shields.io/codecov/c/github/terrylinooo/psr7.svg)](https://codecov.io/gh/terrylinooo/psr7) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

This library is a PSR-7 implementation used by [Shieldon](https://github.com/terrylinooo/shieldon) firewall 2 version, following up the PSR-7 [HTTP message interfaces](https://www.php-fig.org/psr/psr-7/) document. You can use it on any framework which is compatible with the PSR-7 standard.

## Install

```php
composer require shieldon/psr7
```

The Shieldon PSR-7 implementation requires at least PHP 7.1 to run.
The usages of every method can be found in the [unit tests](https://github.com/terrylinooo/psr7/tree/master/tests/Psr7).

## Examples

Here are some examples that show you the way creating PSR-7 instances from PSR-17 HTTP factories. 
### Create a server request

```php
use Shieldon\Psr7\Factory\ServerRequestFactory;

$serverRequestFactory = new ServerRequestFactory(true);
$serverRequest = $serverRequestFactory->createServerRequest('', '');
```

### Create a client request

```php
use Shieldon\Psr7\Factory\RequestFactory;

$requestFactory = new RequestFactory();
$request = $requestFactory->createRequest('GET', 'https://www.google.com');
```

### Create a server response

```php
use Shieldon\Psr7\Factory\ResponseFactory;

 $responseFactory = new ResponseFactory();
$response = $responseFactory->createResponse(200, 'OK');
```



