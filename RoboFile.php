<?php

require __DIR__ . '/vendor/vysokeskoly/deb-build/src/autoload.php';

use Robo\Common\ResourceExistenceChecker;
use Robo\Symfony\ConsoleIO;
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
    const VHOST_DIR = 'etc/apache2/sites-enabled';
    const POSTINST_DIR = 'etc';

    const BIN_CONSOLE = 'php ./bin/console';
    const BIN_CONSOLE_HIGH_MEMORY = 'php -d memory_limit=256M ./bin/console';
    const ENV_PROD = '.env.prod';

    /**
     * Build deb package. It is expected the Composer packages were installed using `--no-dev`.
     *
     * @param array $options
     * @return int
     */
    public function buildDeb(ConsoleIO $io, $options = ['dev-build' => false])
    {
        $this->stopOnFail();

        $isDevBuild = (bool) $options['dev-build'];

        if (!$this->checkFpmIsInstalled()) {
            return 1;
        }

        if (!$this->isFile(self::ENV_PROD)) {
            $io->error(sprintf('File %s does not exists.', self::ENV_PROD));

            return 1;
        }

        $sourceDir = __DIR__;
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
        $postinstResult = $this->taskPostinst(
            $packageName,
            $appInstallDir . '/' . self::POSTINST_DIR,
            self::INSTALL_DIR
        )
            ->args([
                'www-data', // runtime files owner
                'www-data', // runtime files group
            ])
            ->run();

        $postinstPath = $postinstResult['path'];

        // Copy required directories
        foreach ([
                     'config',
                     'src',
                     'templates',
                     'translations',
                     'vendor',
                     'public',
                 ] as $directoryToCopy) {
            $this->_copyDir($sourceDir . '/' . $directoryToCopy, $appInstallDir . '/' . $directoryToCopy);
        }

        // Copy required files
        foreach (['bin/console', 'bin/robo', 'composer.json', 'composer.lock', 'RoboFile.php'] as $fileToCopy) {
            $this->_copy($sourceDir . '/' . $fileToCopy, $appInstallDir . '/' . $fileToCopy);
        }

        // Create prod .env file
        $this->_copy($sourceDir . self::ENV_PROD, $appInstallDir . '/.env');

        // Generate buildinfo.xml
        $this->taskBuildinfo($appInstallDir . '/var/buildinfo.xml')
            ->appName($packageName)
            ->version($packageVersion . '-' . $versionIteration)
            ->run();

        // Even when packages are installed using `composer install --no-dev`, they often contains unneeded files.
        $vendorDirectoriesToDelete = [
            'ocramius/proxy-manager/html-docs',
            'ocramius/proxy-manager/tests',
            'twig/twig/test',
            'guzzlehttp/guzzle/docs',
            'guzzlehttp/guzzle/tests',
            'monolog/monolog/tests',
            'mobiledetect/mobiledetectlib/tests',
        ];

        // Clean unwanted vendor directories
        foreach ($vendorDirectoriesToDelete as $vendorDirectoryToDelete) {
            $this->_deleteDir($appInstallDir . '/vendor/' . $vendorDirectoryToDelete);
        }

        $this->taskFilesystemHelper()
            ->dir($appInstallDir)
            ->removeDirsRecursively('Tests', 'vendor/symfony')// Remove Tests files from Symfony itself
            ->run();

        // Copy vhosts settings
        $vhostDir = $buildRootDir . '/' . self::VHOST_DIR;
        $this->taskFilesystemStack()
            ->mkdir($vhostDir)
            ->copy(
                __DIR__ . '/etc/vhosts/vhosts.image-api.conf',
                $vhostDir . '/vhosts.image-api.conf'
            )
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
            // PHP Extensions
            ->args(['--depends', 'php-common'])
            ->args(['--depends', 'php-cli'])
            ->args(['--depends', 'vysokeskoly-apache-common'])
            ->args(['--deb-activate', 'apache-common-reload'])
            // Post install
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

        $installDir = '/' . self::INSTALL_DIR;

        // Setup rights recursively
        $directoriesToChmod = [
            $installDir,
            '/' . self::VHOST_DIR,
        ];
        foreach ($directoriesToChmod as $directoryToChmod) {
            $this->taskFilesystemHelper()
                ->dir($directoryToChmod)
                ->chmodRecursivelyWritableByUserReadableByOthers()
                ->run();
        }

        // Do hard cache clean
        $cacheDir = $installDir . '/var/cache';
        if ($this->isFile($cacheDir)) {
            $this->_cleanDir($cacheDir);
        }

        // Clean and warm-up app cache
        foreach (['dev', 'prod'] as $symfonyEnvironment) {
            $this->taskExec('php -d memory_limit=256M ./bin/console')
                ->arg('cache:clear')
                ->arg('--env=' . $symfonyEnvironment)
                ->dir($installDir)
                ->run();
        }

        // Make var/ directory (containing cache and logs) recursively owned and writable for given user
        $varDirectory = $installDir . '/var';
        $this->taskFilesystemStack()
            ->chown($varDirectory, $runtimeFilesOwner, true)
            ->chgrp($varDirectory, $runtimeFilesOwner, true)// group is the same as user
            ->chmod($varDirectory, 0755)// writable by user
            ->run();

        $this->taskFilesystemHelper()
            ->dir($varDirectory)
            ->chmodRecursivelyWritableByUserReadableByOthers()
            ->run();

        // Copy assets
        $this->taskExec('php ./bin/console assets:install ./public')
            ->dir($installDir)
            ->run();

        return 0;
    }
}
