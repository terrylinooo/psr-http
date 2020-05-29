<?php 
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shieldon\Psr7;

use PHPUnit\Framework\TestCase;
use Shieldon\Psr7\Uri;

class UriTest extends TestCase
{
    function testInit()
    {
        $uri = new Uri('http://jack:1234@example.com/demo/?test=5678&test2=90#section-1');
    }
}
