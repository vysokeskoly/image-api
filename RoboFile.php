<?php

require __DIR__ . '/vendor/vysokeskoly/deb-build/src/autoload.php';

use Robo\Common\ResourceExistenceChecker;
use Robo\Tasks;
use VysokeSkoly\Build\ComposerParserTrait;
use VysokeSkoly\Build\FpmCheckerTrait;
use VysokeSkoly\Build\PackageVersionerTrait;
use VysokeSkoly\Build\Task\LoadTasksTrait;

class RoboFile extends Tasks
{
    use ComposerParserTrait;
    use FpmCheckerTrait;
    use PackageVersionerTrait;
    use ResourceExistenceChecker;
    use LoadTasksTrait;

    const INSTALL_DIR = 'srv/www/image-api';
    const POSTINST_DIR = 'etc';

    const BIN_CONSOLE = 'php ./bin/console';
    const BIN_CONSOLE_HIGH_MEMORY = 'php -d memory_limit=256M ./bin/console';

    /**
     * Build deb package. It is expected the Composer packages were installed using `--no-dev`.
     *
     * @param array $options
     * @return int
     */
    public function buildDeb($options = ['dev-build' => false])
    {
        $this->stopOnFail();

        $isDevBuild = (bool) $options['dev-build'];

        if (!$this->checkFpmIsInstalled()) {
            return 1;
        }

        $packageName = 'vysokeskoly-image-api';
        $packageVersion = $this->assemblePackageVersion($isDevBuild);
        $versionIteration = $this->assembleVersionIteration();
        $composer = $this->parseComposer();

        $temporaryBuildDir = $this->_tmpDir();
        $buildRootDir = $temporaryBuildDir . '/root';
        $appInstallDir = $buildRootDir . '/' . self::INSTALL_DIR;

        // Create basic filesystem structure
        $this->taskFilesystemStack()
            ->mkdir($appInstallDir)
            ->mkdir($appInstallDir . '/var')
            ->mkdir($appInstallDir . '/' . self::POSTINST_DIR)
            ->run();

        // Generate postinst script
        $postinstResult = $this->taskPostinst($packageName, $appInstallDir . '/' . self::POSTINST_DIR,
            self::INSTALL_DIR)
            ->args([
                'www-data', // runtime files owner
                'www-data', // runtime files group
            ])
            ->run();

        $postinstPath = $postinstResult['path'];

        // Copy required directories
        foreach (['app', 'bin', 'src', 'vendor', 'web'] as $directoryToCopy) {
            $this->_copyDir(__DIR__ . '/' . $directoryToCopy, $appInstallDir . '/' . $directoryToCopy);
        }

        // Copy required files
        foreach (['robo.phar', 'composer.json', 'composer.lock', 'RoboFile.php'] as $fileToCopy) {
            $this->_copy(__DIR__ . '/' . $fileToCopy, $appInstallDir . '/' . $fileToCopy);
        }

        // Generate buildinfo.xml
        $this->taskBuildinfo($appInstallDir . '/var/buildinfo.xml')
            ->appName($packageName)
            ->version($packageVersion . '-' . $versionIteration)
            ->run();

        // Even when packages are installed using `composer install --no-dev`, they often contains unneeded files.
        $vendorDirectoriesToDelete = [
            'twig/twig/test',
            'guzzle/guzzle/docs',
            'guzzle/guzzle/tests',
            'monolog/monolog/tests',
        ];

        // Clean unwanted vendor directories
        foreach ($vendorDirectoriesToDelete as $vendorDirectoryToDelete) {
            $this->_deleteDir($appInstallDir . '/vendor/' . $vendorDirectoryToDelete);
        }

        $this->taskFilesystemHelper()
            ->dir($appInstallDir)
            ->removeDirsRecursively('Tests', 'vendor/symfony')// Remove Tests files from Symfony itself
            ->run();

        $this->taskExec('fpm')
            ->args(['--description', $composer['description']])// description for `apt search`
            ->args(['-s', 'dir'])// source type
            ->args(['-t', 'deb'])// output type
            ->args(['--name', $packageName])// package name
            ->args(['--vendor', 'VysokeSkoly'])
            ->args(['--architecture', 'all'])
            ->args(['--version', $packageVersion])
            ->args(['--iteration', $versionIteration])
            ->args(['-C', $buildRootDir])// change directory to here before searching for files
            ->args(['--depends', 'php-common'])
            ->args(['--depends', 'php-cli'])
            ->args(['--depends', 'vysokeskoly-apache-common'])
            ->args(['--deb-activate', 'apache-common-reload'])
            ->args(['--after-install', $postinstPath])
            // Files placed in /etc wouldn't be overridden on package update without following flag:
            ->arg('--deb-no-default-config-files')
            ->arg('.')
            ->run();

        $this->io()->success('Done');

        return 0;
    }

    /**
     * Run post-installation tasks for deb package
     *
     * @param string $runtimeFilesOwner name of the user to whom should the files created on runtime belong to
     * @param string $runtimeFilesGroup name of the group to whom should the files created on runtime belong to
     */
    public function installDebPostinst($runtimeFilesOwner, $runtimeFilesGroup)
    {
        $this->stopOnFail();

        // Setup rights recursively
        $directoriesToChmod = [
            '/' . self::INSTALL_DIR,
        ];
        foreach ($directoriesToChmod as $directoryToChmod) {
            $this->taskFilesystemHelper()
                ->dir($directoryToChmod)
                ->chmodRecursivelyWritableByUserReadableByOthers()
                ->run();
        }

        // Do hard cache clean
        $cacheDir = '/' . self::INSTALL_DIR . '/var/cache';
        if ($this->isFile($cacheDir)) {
            $this->_cleanDir($cacheDir);
        }

        // Build Symfony bootstrap
        $this->taskExec('php ./vendor/sensio/distribution-bundle/Resources/bin/build_bootstrap.php')
            ->arg('./var')
            ->arg('./app')
            ->arg('--use-new-directory-structure')
            ->dir('/' . self::INSTALL_DIR)
            ->run();

        // Clean and warm-up app cache
        $commands = [
            'cache:clear' => ['--no-warmup'],
            'cache:warmup' => [],
        ];
        foreach (['dev', 'prod'] as $symfonyEnvironment => $withNoDebug) {
            foreach ($commands as $command => $args) {
                $commandArgs = [
                    $command,
                    '--env=' . $symfonyEnvironment,
                ];

                if ($withNoDebug) {
                    $commandArgs[] = '--no-debug';
                }

                $this->taskExec(self::BIN_CONSOLE_HIGH_MEMORY)
                    ->args(array_merge($commandArgs, $args))
                    ->dir('/' . self::INSTALL_DIR)
                    ->run();
            }
        }

        // Make var/ directory (containing cache and logs) recursively owned and writable for given user
        $varDirectory = '/' . self::INSTALL_DIR . '/var';
        $this->taskFilesystemStack()
            ->chown($varDirectory, $runtimeFilesOwner, true)
            ->chgrp($varDirectory, $runtimeFilesGroup, true)
            ->run();

        $this->taskFilesystemHelper()
            ->dir($varDirectory)
            ->chmodRecursivelyWritableByUserReadableByOthers()
            ->run();

        $this->taskExec(self::BIN_CONSOLE)
            ->arg('assets:install')
            ->arg('./web')
            ->dir('/' . self::INSTALL_DIR)
            ->run();
    }
}