<?php

namespace Etu\Core\CoreBundle\Composer;

use Composer\Script\Event;

class InstallationHandler
{
    public static function displayHeader()
    {
        echo "\n  EtuUTT installation manager";
        echo "\n-------------------------------\n";
        echo "\nEtuUTT installation is being checked and corrected if needed ...\n\n";
    }

    public static function installEtuUTT(Event $event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();

        if (! isset($extras['symfony-app-dir'])) {
            throw new \InvalidArgumentException(
                'The parameter "symfony-app-dir" must be configured in composer.json "extra" for EtuUTT installation'
            );
        }

        if (! isset($extras['symfony-web-dir'])) {
            throw new \InvalidArgumentException(
                'The parameter "symfony-web-dir" must be configured in composer.json "extra" for EtuUTT installation'
            );
        }

        $rootDir = __DIR__ . '/../../../../../';
        $appDir = $rootDir . '/' . $extras['symfony-app-dir'];
        $webDir = $rootDir . '/' . $extras['symfony-web-dir'];

        if (file_exists($webDir . '/app.php')) {
            // Installation already done
            return;
        }

        self::createUploadDirectory($webDir);
        self::changePermissions($appDir, $webDir);
        self::createFrontControllers($webDir);
    }

    protected static function createUploadDirectory($webDir)
    {
        echo 'Creating uploads directory ...' . "\n";

        self::recursiveCopy($webDir . '/uploads.dist', $webDir . '/uploads');
    }


    protected static function changePermissions($appDir, $webDir)
    {
        echo 'Changing permissions ...' . "\n";

        // Cache
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($appDir . '/cache'));

        foreach($iterator as $item) {
            chmod($item, 0777);
        }

        // Uploads
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($webDir . '/uploads'));

        foreach($iterator as $item) {
            chmod($item, 0777);
        }
    }

    protected static function createFrontControllers($webDir)
    {
        echo 'Installing front controllers ...' . "\n";

        copy($webDir . '/app.php.dist',         $webDir . '/app.php');
        copy($webDir . '/etu_dev.php.dist',     $webDir . '/etu_dev.php');
    }


    private static function recursiveCopy( $source, $destination ) {
        if ( is_dir( $source ) ) {
            @mkdir( $destination );
            $directory = dir( $source );
            while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
                if ( $readdirectory == '.' || $readdirectory == '..' ) {
                    continue;
                }
                $PathDir = $source . '/' . $readdirectory;
                if ( is_dir( $PathDir ) ) {
                    self::recursiveCopy( $PathDir, $destination . '/' . $readdirectory );
                    continue;
                }
                copy( $PathDir, $destination . '/' . $readdirectory );
            }

            $directory->close();
        }else {
            copy( $source, $destination );
        }
    }
}
