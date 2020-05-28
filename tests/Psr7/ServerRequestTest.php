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
use Shieldon\Psr7\ServerRequest;
use Shieldon\Psr7\UploadedFile;

class ServerRequestTest extends TestCase
{
    

    function testParseUploadedFiles()
    {
        $files = array(
            
            // <input type="file" name="file1">
            'files1' => array(
                'name' => 'example1.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php200A.tmp',
                'error' => 0,
                'size' => 100000,
            ),

            // <input type="file" name="files2[a]">
            // <input type="file" name="files2[b]">
            'files2' => array(
                'name' => array(
                    'a' => 'example21.jpg',
                    'b' => 'example22.jpg',
                ),
                'type' => array(
                    'a' => 'image/jpeg',
                    'b' => 'image/jpeg',
                ),
                'tmp_name' => array(
                    'a' => '/tmp/php343C.tmp',
                    'b' => '/tmp/php343D.tmp',
                ),
                'error' => array(
                    'a' => 0,
                    'b' => 0,
                ),
                'size' => array(
                    'a' => 125100,
                    'b' => 145000,
                ),
            ),

            // <input type="file" name="files3[]">
            // <input type="file" name="files3[]">
            'files3' => array(
                'name' => array(
                    0 => 'example31.jpg',
                    1 => 'example32.jpg',
                ),
                'type' => array(
                    0 => 'image/jpeg',
                    1 => 'image/jpeg',
                ),
                'tmp_name' => array(
                    0 => '/tmp/php310C.tmp',
                    1 => '/tmp/php313D.tmp',
                ),
                'error' => array(
                    0 => 0,
                    1 => 0,
                ),
                'size' => array(
                    0 => 200000,
                    1 => 300000,
                ),
            ),

            // <input type="file" name="files4[details][avatar]">
            
            'files4' => array (
    
                'name' => array (
                    'details' => array (
                        'avatar' => 'my-avatar.png',
                    ),
                ),
                'type' => array (
                    'details' => array (
                        'avatar' => 'image/png',
                    ),
                ),
                'tmp_name' => array (
                    'details' => array (
                        'avatar' => '/tmp/phpmFLrzD',
                    ),
                ),
                'error' => array (
                    'details' => array (
                        'avatar' => 0,
                    ),
                ),
                'size' => array (
                    'details' => array (
                        'avatar' => 90996,
                    ),
                ),
            ),
            
        );

        $results = ServerRequest::parseUploadedFiles($files);

       var_dump($results);

     //   echo json_encode($results, JSON_PRETTY_PRINT);

      //  $d = ServerRequest::x($results);

       // var_dump($d);

    }
}
