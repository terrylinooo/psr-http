<?php
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

/**
 * Register to PSR-4 autoloader.
 *
 * @return void
 */
function psr_http_register()
{
    spl_autoload_register('psr_http_autoload', true, false);
}

/**
 * PSR-4 autoloader.
 *
 * @param string $className
 * 
 * @return void
 */
function psr_http_autoload($className)
{
    $prefix = 'Shieldon\\';
    $dir = __DIR__ . '/src';

    if (0 === strpos($className, $prefix . 'Psr')) {
        $parts = explode('\\', substr($className, strlen($prefix)));
        $filepath = $dir . '/' . implode('/', $parts) . '.php';

        if (is_file($filepath)) {
            require $filepath;
        }
    }
}

psr_http_register();