<?php

declare(strict_types=1);

namespace HF\GitHooks;

use Composer\Script\Event;
use Exception;

define('ROOT_DIR', __DIR__ . '/../../../../');

class Hooks
{
    public static function preHooks(Event $event): bool
    {
        $io      = $event->getIO();
        $gitHook = ROOT_DIR . '.git/hooks/pre-push';

        if (file_exists($gitHook)) {
            unlink($gitHook);
            $io->write('<info>Pre-push commit hook removed!</info>');
        }

        return true;
    }

    public static function postHooks(Event $event): bool
    {
        if (! file_exists(ROOT_DIR . '.git')) {
            throw new Exception(sprintf('Local GIT repository not found'));
        }

        // not everywhere hooks are available (gitlab, travis?), so bail
        if (! is_dir(ROOT_DIR . '.git/hooks')) {
            return true;
        }

        $io      = $event->getIO();
        $gitHook = ROOT_DIR . '.git/hooks/pre-push';
        $docHook = ROOT_DIR . 'vendor/plhw/hf-git-hooks/hooks/pre-push.php';

        copy($docHook, $gitHook);
        chmod($gitHook, 0777);

        $io->write('<info>Pre-push hook installed!</info>');

        return true;
    }
}
