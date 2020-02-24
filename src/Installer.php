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
 *
 * @package   plhw/hf-git-hooks
 */

declare(strict_types=1);

namespace HF\GitHooks;

use Composer\Script\Event;

class Installer
{
    private static $rootDir = __DIR__ . '/../../../../';

    private static $hooks = ['pre-push', 'pre-commit'];

    public static function preHooks(Event $event): bool
    {
        // not everywhere hooks are available (gitlab, travis?), so bail
        if (! \is_dir(self::$rootDir . '.git/hooks')) {
            return true;
        }

        $io = $event->getIO();

        foreach (self::$hooks as $hook) {
            $gitHook = \sprintf('%s.git/hooks/%s', self::$rootDir, $hook);

            if (! \file_exists($gitHook)) {
                continue;
            }

            if (\unlink($gitHook)) {
                $io->write(\sprintf('<info>git hook "%s" removed</info>', $hook));
            } else {
                $io->write(\sprintf('<error>git hook "%s" could not be removed...</error>', $hook));
            }
        }

        return true;
    }

    public static function postHooks(Event $event): bool
    {
        // not everywhere hooks are available (gitlab, travis?), so bail
        if (! \is_dir(self::$rootDir . '.git/hooks')) {
            return true;
        }

        $io = $event->getIO();

        foreach (self::$hooks as $hook) {
            $gitHook = \sprintf('%s.git/hooks/%s', self::$rootDir, $hook);
            $vendorHook = \sprintf('%svendor/plhw/hf-git-hooks/hooks/%s.php', self::$rootDir, $hook);

            if (\copy($vendorHook, $gitHook) && \chmod($gitHook, 0777)) {
                $io->write(\sprintf('<info>git hook "%s" installed</info>', $hook));
            } else {
                $io->write(\sprintf('<error>git hook "%s" could not be installed...</error>', $hook));
            }
        }

        return true;
    }
}
