#!/usr/bin/env php
<?php

define('VENDOR_DIR', __DIR__ . '/../../vendor');

require VENDOR_DIR . '/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CodeQualityTool extends Application
{
    private $output;
    private $input;

    const PHP_FILES_IN_SRC     = '/^src\/(.*)(\.php)$/';
    const PHP_FILES_IN_CLASSES = '/^classes\/(.*)(\.php)$/';

    public function __construct()
    {
        parent::__construct('Code Quality Tool', '1.0.0');
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        $output->writeln('<info>Fetching files</info>');
        $files = $this->extractCommitedFiles();

        $output->writeln('<info>Check composer</info>');
        if (! $this->checkComposer($files)) {
            throw new Exception(sprintf('There is a problem with your composer (lock) file!'));
        }

        $output->writeln('<info>Running PHPLint</info>');
        if (! $this->phpLint($files)) {
            throw new Exception('There are some PHP syntax errors!');
        }

        $output->writeln('<info>Checking code style</info>');
        if (! $this->codeStyle($files)) {
            throw new Exception(sprintf('There are coding standards violations!'));
        }

//        $output->writeln('<info>Running unit tests</info>');
//        if (! $this->unitTests()) {
//            throw new Exception('Unit tests are failing...');
//        }
    }

    private function extractCommitedFiles(): array
    {
        /*
         * The SHA1 ID of an empty branch.
         */
        define('SHA1_EMPTY', '0000000000000000000000000000000000000000');

        $fileList = [];

        // Loop over the commits.
        while ($commit = trim(fgets(STDIN))) {
            list($local_ref, $local_sha, $remote_ref, $remote_sha) = explode(' ', $commit);

            // Skip the coding standards check if we are deleting a branch or if there is
            // no local branch.
            if ('(delete)' === $local_ref || SHA1_EMPTY === $local_sha) {
                exit(0);
            }

            // Escape shell command arguments. These should normally be safe since they
            // only contain SHA numbers, but you never know.
            foreach (['local_sha', 'remote_sha'] as $argument) {
                $$argument = escapeshellcmd($$argument);
            }

            $command  = "git diff-tree --no-commit-id --name-only -r '$local_sha' '$remote_sha'";
            $fileList = array_merge($fileList, explode("\n", `$command`));
        }

        // Remove duplicates, empty lines and files that no longer exist in the branch.
        $fileList = array_unique(array_filter($fileList, function ($file) {
            return ! empty($file) && file_exists($file);
        }));

        return $fileList;
    }

    private function checkComposer(array $files): bool
    {
        $composerJsonDetected = false;
        $composerLockDetected = false;

        foreach ($files as $file) {
            $composerJsonDetected = ('composer.json' === $file) || $composerJsonDetected;
            $composerLockDetected = ('composer.lock' === $file) || $composerLockDetected;
        }

        if ($composerJsonDetected && ! $composerLockDetected) {
            return false;
        }

        if ($composerJsonDetected || $composerLockDetected) {
            $process = new Process(['composer', 'validate']);
            $process->run();

            if (! $process->isSuccessful()) {
                if (false !== strpos($process->getErrorOutput(), 'The lock file is not up to date with the latest changes in composer.json')) {
                    $process = new Process(['composer', 'update', '--lock']);
                    $process->run();
                }

                $this->output->writeln(sprintf('<error>%s</error>', trim($process->getErrorOutput())));

                return false;
            }
        }

        return true;
    }

    private function phpLint(array $files): bool
    {
        $needle  = '/(\.php)|(\.inc)$/';
        $succeed = true;

        foreach ($files as $file) {
            if (! preg_match($needle, $file)) {
                continue;
            }

            $process = new Process(['php', '-l', $file]);
            $process->run();

            if (! $process->isSuccessful()) {
                $this->output->writeln($file);
                $this->output->writeln(sprintf('<error>%s</error>', trim($process->getErrorOutput())));

                if ($succeed) {
                    $succeed = false;
                }
            }
        }

        return $succeed;
    }

    private function unitTests(): bool
    {
        $filePhpunit = VENDOR_DIR . '/../phpunit.xml';

        if (file_exists($filePhpunit) || file_exists($filePhpunit . '.dist')) {
            $process = new Process(['php', VENDOR_DIR . '/bin/phpunit']);
            $process->setWorkingDirectory(__DIR__ . '/../..');
            $process->setTimeout(3600);

            $phpunit->run(function ($type, $buffer) {
                $this->output->write($buffer);
            });

            return $phpunit->isSuccessful();
        }

        $this->output->writeln(sprintf('<fg=yellow>%s</>', 'Not PHPUnit!'));

        return true;
    }

    private function codeStyle(array $files): bool
    {
        $succeed = true;
        $needle  = self::PHP_FILES_IN_SRC;

        foreach ($files as $file) {
            if (! preg_match($needle, $file)) {
                continue;
            }

            $phpCsFixer = new Process([VENDOR_DIR . '/bin/php-cs-fixer', 'fix', $file, '--config=.php_cs', '-v', '--dry-run', '-stop-on-violation', '--using-cache=no']);
            $phpCsFixer->setWorkingDirectory(__DIR__ . '/../../');
            $phpCsFixer->run();

            if (! $phpCsFixer->isSuccessful()) {
                $this->output->writeln(sprintf('<error>%s</error>', trim($phpCsFixer->getOutput())));

                if ($succeed) {
                    $succeed = false;
                }
            }
        }

        return $succeed;
    }
}

(new CodeQualityTool())->run();
