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
use Shieldon\Psr7\UploadedFile;
use Shieldon\Psr7\Utils\UploadedFileHelper;

class UploadedFileHelperTest extends TestCase
{
    public function test_ParseUploadedFiles()
    {
        $files = [
            
            // <input type="file" name="file1">

            'files1' => [
                'name' => 'example1.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php200A.tmp',
                'error' => 0,
                'size' => 100000,
            ],

            // <input type="file" name="files2[a]">
            // <input type="file" name="files2[b]">

            'files2' => [
                'name' => [
                    'a' => 'example21.jpg',
                    'b' => 'example22.jpg',
                ],
                'type' => [
                    'a' => 'image/jpeg',
                    'b' => 'image/jpeg',
                ],
                'tmp_name' => [
                    'a' => '/tmp/php343C.tmp',
                    'b' => '/tmp/php343D.tmp',
                ],
                'error' => [
                    'a' => 0,
                    'b' => 0,
                ],
                'size' => [
                    'a' => 125100,
                    'b' => 145000,
                ],
            ],

            // <input type="file" name="files3[]">
            // <input type="file" name="files3[]">

            'files3' => [
                'name' => [
                    0 => 'example31.jpg',
                    1 => 'example32.jpg',
                ],
                'type' => [
                    0 => 'image/jpeg',
                    1 => 'image/jpeg',
                ],
                'tmp_name' => [
                    0 => '/tmp/php310C.tmp',
                    1 => '/tmp/php313D.tmp',
                ],
                'error' => [
                    0 => 0,
                    1 => 0,
                ],
                'size' => [
                    0 => 200000,
                    1 => 300000,
                ],
            ],

            // <input type="file" name="files4[details][avatar]">
            
            'files4' => [
                'name' => [
                    'details' => [
                        'avatar' => 'my-avatar.png',
                    ],
                ],
                'type' => [
                    'details' => [
                        'avatar' => 'image/png',
                    ],
                ],
                'tmp_name' => [
                    'details' => [
                        'avatar' => '/tmp/phpmFLrzD',
                    ],
                ],
                'error' => [
                    'details' => [
                        'avatar' => 0,
                    ],
                ],
                'size' => [
                    'details' => [
                        'avatar' => 90996,
                    ],
                ],
            ],
        ];

        $expectedFiles = [
            'files1' => [
                'name' => 'example1.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php200A.tmp',
                'error' => 0,
                'size' => 100000,
            ],
            'files2' => [
                'a' => [
                    'tmp_name' => '/tmp/php343C.tmp',
                    'name' => 'example21.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 125100,
                ],
                'b' => [
                    'tmp_name' => '/tmp/php343D.tmp',
                    'name' => 'example22.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 145000,
                ],
            ],
            'files3' => [
                0 => [
                    'tmp_name' => '/tmp/php310C.tmp',
                    'name' => 'example31.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 200000,
                ],
                1 => [
                    'tmp_name' => '/tmp/php313D.tmp',
                    'name' => 'example32.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 300000,
                ],
            ],
            'files4' => [
                'details' => [
                    'avatar' => [
                        'tmp_name' => '/tmp/phpmFLrzD',
                        'name' => 'my-avatar.png',
                        'type' => 'image/png',
                        'error' => 0,
                        'size' => 90996,
                    ],
                ],
            ],
        ];

        $results = UploadedFileHelper::uploadedFileParse($files);

        $this->assertEquals($results, $expectedFiles);
    }
    public function test_UploadedFileSpecsConvert()
    {
        $formattedFiles = [
            'files1' => [
                'name' => 'example1.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/php200A.tmp',
                'error' => 0,
                'size' => 100000,
            ],
            'files2' => [
                'a' => [
                    'tmp_name' => '/tmp/php343C.tmp',
                    'name' => 'example21.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 125100,
                ],
                'b' => [
                    'tmp_name' => '/tmp/php343D.tmp',
                    'name' => 'example22.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 145000,
                ],
            ],
            'files3' => [
                0 => [
                    'tmp_name' => '/tmp/php310C.tmp',
                    'name' => 'example31.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 200000,
                ],
                1 => [
                    'tmp_name' => '/tmp/php313D.tmp',
                    'name' => 'example32.jpg',
                    'type' => 'image/jpeg',
                    'error' => 0,
                    'size' => 300000,
                ],
            ],
            'files4' => [
                'details' => [
                    'avatar' => [
                        'tmp_name' => '/tmp/phpmFLrzD',
                        'name' => 'my-avatar.png',
                        'type' => 'image/png',
                        'error' => 0,
                        'size' => 90996,
                    ],
                ],
            ],
        ];

        $expectedFiles = [
            'files1' => new UploadedFile(
                '/tmp/php200A.tmp',
                'example1.jpg',
                'image/jpeg',
                100000,
                0
            ),
            'files2' => [
                'a' => new UploadedFile(
                    '/tmp/php343C.tmp',
                    'example21.jpg',
                    'image/jpeg',
                    125100,
                    0
                ),
                'b' => new UploadedFile(
                    '/tmp/php343D.tmp',
                    'example22.jpg',
                    'image/jpeg',
                    145000,
                    0
                )
            ],
            'files3' => [
                0 => new UploadedFile(
                    '/tmp/php310C.tmp',
                    'example31.jpg',
                    'image/jpeg',
                    200000,
                    0
                ),
                1 => new UploadedFile(
                    '/tmp/php313D.tmp',
                    'example32.jpg',
                    'image/jpeg',
                    300000,
                    0
                )
                ],
            'files4' => [
                'details' => [
                    'avatar' => new UploadedFile(
                        '/tmp/phpmFLrzD',
                        'my-avatar.png',
                        'image/png',
                        90996,
                        0
                    )
                ],
            ],
        ];

        $results = UploadedFileHelper::uploadedFileSpecsConvert($formattedFiles);

        $this->assertEquals($results, $expectedFiles);
    }
}
