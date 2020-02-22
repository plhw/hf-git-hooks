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

class Hooks
{
    private static $rootDir = __DIR__ . '/../../../../';

    public static function preHooks(Event $event): bool
    {
        $io = $event->getIO();
        $gitHook = self::$rootDir . '.git/hooks/pre-push';

        if (\file_exists($gitHook)) {
            \unlink($gitHook);
            $io->write('<info>Pre-push commit hook removed!</info>');
        }

        return true;
    }

    public static function postHooks(Event $event): bool
    {
        if (! \file_exists(self::$rootDir . '.git')) {
            return true;
        }

        // not everywhere hooks are available (gitlab, travis?), so bail
        if (! \is_dir(self::$rootDir . '.git/hooks')) {
            return true;
        }

        $io = $event->getIO();
        $gitHook = self::$rootDir . '.git/hooks/pre-push';
        $docHook = self::$rootDir . 'vendor/plhw/hf-git-hooks/hooks/pre-push.php';

        \copy($docHook, $gitHook);
        \chmod($gitHook, 0777);

        $io->write('<info>Pre-push hook installed!</info>');

        return true;
    }
}
