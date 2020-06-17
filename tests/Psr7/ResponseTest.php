<?php 
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Test\Psr7;

use PHPUnit\Framework\TestCase;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Shieldon\Psr7\Response;
use InvalidArgumentException;

class ResponseTest extends TestCase
{
    public function test__construct()
    {
        $response = new Response();

        $this->assertTrue(($response instanceof ResponseInterface));
        $this->assertTrue(($response instanceof MessageInterface));

        $newResponse = $response->withStatus(555, 'Custom reason phrase');

        $this->assertSame($newResponse->getStatusCode(), 555);
        $this->assertSame($newResponse->getReasonPhrase(), 'Custom reason phrase');

        $new2Response = $newResponse->withStatus(500);

        $this->assertSame($new2Response->getStatusCode(), 500);
        $this->assertSame($new2Response->getReasonPhrase(), 'Internal Server Error');
    }

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    */

    public function test_Exception_AssertStatus_InvalidRange()
    {
        $this->expectException(InvalidArgumentException::class);

        // Exception:
        // => Status code should be in a range of 100-599, but 600 provided.
        $response = new Response(600);
    }

    public function test_Exception_AssertStatus_InvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new Response();

        // Exception:
        // => Status code should be an integer value, but string provided.
        $newResponse = $response->withStatus("500", 'Custom reason phrase');
    }

    public function test_Exception_assertReasonPhrase_InvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new Response();

        // Exception:
        // => Reason phrase must be a string, but integer provided.
        $newResponse = $response->withStatus(200, 12345678);
    }

    public function test_Exception_assertReasonPhrase_ProhibitedCharacter()
    {
        $this->expectException(InvalidArgumentException::class);

        $response = new Response();

        // Exception:
        // => Reason phrase contains "\r" that is considered as a prohibited character.
        $newResponse = $response->withStatus(200, 'Custom reason phrase\n\rThe next line');
    }
}
