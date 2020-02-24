#!/usr/bin/env php
<?php

/**
 * Project 'Healthy Feet' by Podolab Hoeksche Waard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see       https://plhw.nl/
 *
 * @copyright Copyright (c) 2010 bushbaby multimedia. (https://bushbaby.nl)
 * @author    Bas Kamer <bas@bushbaby.nl>
 * @license   Proprietary License
 */

declare(strict_types=1);

namespace HF\GitHooks;

use Exception;

(function () {
    $dir = \realpath(__DIR__);
    while (! \file_exists($dir . '/vendor/autoload.php')) {
        if ('/' === $dir) {
            throw new Exception('No vendor/autoload.php detected...');
        }

        $dir = \dirname($dir);
    }

    \chdir($dir);
    require $dir . '/vendor/autoload.php';
})();


return (new Runner('pre-commit'))->run();
