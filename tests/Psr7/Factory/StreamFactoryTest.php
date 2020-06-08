<?php 
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Psr7\Factory;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Shieldon\Psr7\Factory\StreamFactory;

class StreamFactoryTest extends TestCase
{
    public function test_createStream()
    {
        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStream('Foo Bar');

        ob_start();
        echo $stream;
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertSame('Foo Bar', $output);
    }

    public function test_createStreamFromFile()
    {
        $sourceFile = BOOTSTRAP_DIR . '/sample/shieldon_logo.png';

        $streamFactory = new StreamFactory();
        $stream =  $streamFactory->createStreamFromFile($sourceFile);
        $this->assertTrue(($stream instanceof StreamInterface));
        $this->assertSame($stream->getSize(), 15166);
    }
}
