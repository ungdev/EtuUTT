<?php

namespace Etu\Install;

class Process
{
    public static function installDependencies()
    {
        passthru('composer install');
    }

    public static function buildDefaultFiles($rootDir)
    {
        $webDir = $rootDir . '/web';

        self::recursiveCopy($webDir . '/uploads.dist', $webDir . '/uploads');

        copy($webDir . '/app.php.dist',         $webDir . '/app.php');
        copy($webDir . '/etu_dev.php.dist',     $webDir . '/etu_dev.php');

        echo "Done\n";
    }

    public static function changePermissions($rootDir)
    {
        $appDir = $rootDir . '/app';
        $webDir = $rootDir . '/web';

        // Cache
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($appDir . '/cache'));

        foreach($iterator as $item) {
            chmod($item, 0777);
        }

        // Logs
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($appDir . '/logs'));

        foreach($iterator as $item) {
            chmod($item, 0777);
        }

        // Uploads
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($webDir . '/uploads'));

        foreach($iterator as $item) {
            chmod($item, 0777);
        }

        echo "Done\n";
    }

    public static function clearCache()
    {
        passthru('php app/console cache:clear --env=dev');
    }

    public static function createDatabase()
    {
        passthru('php app/console doctrine:schema:update --force --env=dev');
    }

    public static function installBower($rootDir)
    {
        chdir($rootDir . '/web');
        passthru('bower install --force');
        chdir($rootDir);
    }

    public static function installAssets()
    {
        passthru('php app/console assets:install web --env=dev');
        passthru('php app/console assetic:dump --env=dev');
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