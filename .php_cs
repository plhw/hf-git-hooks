<?php

/**
 * Project 'Healthy Feet' by Podolab Hoeksche Waard.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see       https://plhw.nl/
 *
 * @copyright Copyright (c) 2010 - 2018 bushbaby multimedia. (https://bushbaby.nl)
 * @author    Bas Kamer <bas@bushbaby.nl>
 * @license   Proprietary License
 *
 * @package   plhw/hf-git-hooks
 */

declare(strict_types=1);

$config = new HF\CS\Config();
$config->getFinder()->in(__DIR__)->append(['.php_cs']);

$cacheDir = \getenv('TRAVIS') ? \getenv('HOME') . '/.php-cs-fixer' : __DIR__;

$config->setCacheFile($cacheDir . '/.php_cs.cache');

return $config;
