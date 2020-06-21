<?php

declare(strict_types=1);

date_default_timezone_set('UTC');

define('BOOTSTRAP_DIR', __DIR__);

/**
 * Create a writable directrory for unit testing.
 *
 * @param string $filename File name.
 *
 * @return string The file's path.
 */
function save_testing_file(string $filename, string $dir = ''): string
{
    $dir = BOOTSTRAP_DIR . '/../tmp';

    if ($dir === '') {
        $dir = BOOTSTRAP_DIR . '/../tmp/' . $dir;
    }

    if (!is_dir($dir)) {
        $originalUmask = umask(0);
        $result = @mkdir($dir, 0777, true);
        umask($originalUmask);
    }

    return $dir . '/' . $filename;
}


