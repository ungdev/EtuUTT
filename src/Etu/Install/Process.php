<?php

namespace Etu\Install;

use Symfony\Component\Yaml\Yaml;

class Process
{
    public static function askConfig()
    {
        $config = [
            'database' => [
                'driver' => 'pdo_mysql',
                'host' => '127.0.0.1',
                'port' => '3306',
                'name' => 'etuutt',
                'user' => 'root',
                'pass' => '',
            ],
            'first_user' => [
                'login' => '',
                'password' => '',
                'firstName' => '',
                'lastName' => '',
                'email' => '',
            ],
        ];

        $correct = false;

        while (!$correct) {
            /*
             * Database
             */
            echo " Database parameters:\n\n";

            // Driver
            echo '    Driver [pdo_mysql]: ';

            $dbDriver = self::prompt();

            if (!empty($dbHost)) {
                $config['database']['driver'] = $dbDriver;
            }

            // Host
            echo '    Host [127.0.0.1]: ';

            $dbHost = self::prompt();

            if (!empty($dbHost)) {
                $config['database']['host'] = $dbHost;
            }

            // Port
            echo '    Port [3306]: ';

            $dbPort = self::prompt();

            if (!empty($dbPort)) {
                $config['database']['port'] = $dbPort;
            }

            // Name
            echo '    Database name [etuutt]: ';

            $dbName = self::prompt();

            if (!empty($dbName)) {
                $config['database']['name'] = $dbName;
            }

            // User
            echo '    Username [root]: ';

            $dbUser = self::prompt();

            if (!empty($dbUser)) {
                $config['database']['user'] = $dbUser;
            }

            // Password
            echo '    Password []: ';

            $dbPass = self::prompt();

            if (!empty($dbPass)) {
                $config['database']['pass'] = $dbPass;
            }

            /*
             * First user
             */
            echo "\n\n Initial EtuUTT user (for first connexion):\n\n";

            // User
            do {
                echo '    Login: ';
                $fuLogin = self::prompt();
            } while (empty($fuLogin));

            $config['first_user']['login'] = $fuLogin;

            // Password
            do {
                echo '    Password: ';
                $fuPassword = self::prompt();
            } while (empty($fuPassword));

            $config['first_user']['password'] = $fuPassword;

            // First name
            do {
                echo '    First name: ';
                $fuFirstName = self::prompt();
            } while (empty($fuFirstName));

            $config['first_user']['firstName'] = $fuFirstName;

            // Last name
            do {
                echo '    Last name: ';
                $fuLastName = self::prompt();
            } while (empty($fuLastName));

            $config['first_user']['lastName'] = $fuLastName;

            // Last name
            do {
                echo '    Public e-mail: ';
                $fuMail = self::prompt();
            } while (empty($fuMail));

            $config['first_user']['email'] = $fuMail;

            /*
             * First user
             */
            echo "\n\n Are these informations correct? [Y/n] ";

            $correct = (self::prompt() != 'n');

            if (!$correct) {
                echo "\n";
            }
        }

        /*
         * Generate secret
         */
        $config['secret'] = md5(time().uniqid().$config['first_user']['password']);

        return $config;
    }

    public static function installDependencies()
    {
        passthru('composer install');
    }

    public static function requireDependencies($rootDir)
    {
        require $rootDir.'/vendor/autoload.php';
    }

    public static function clearCache()
    {
        passthru('php app/console cache:clear --env=dev');
    }

    public static function buildConfig($config, $rootDir)
    {
        $dist = Yaml::parse($rootDir.'/app/config/parameters.yml.dist');

        $dist['parameters']['database_driver'] = $config['database']['driver'];
        $dist['parameters']['database_host'] = $config['database']['host'];
        $dist['parameters']['database_port'] = (int) $config['database']['port'];
        $dist['parameters']['database_name'] = $config['database']['name'];
        $dist['parameters']['database_user'] = $config['database']['user'];
        $dist['parameters']['database_password'] = $config['database']['pass'];
        $dist['parameters']['secret'] = $config['secret'];

        file_put_contents($rootDir.'/app/config/parameters.yml', Yaml::dump($dist));
    }

    public static function buildDefaultFiles($rootDir)
    {
        $webDir = $rootDir.'/web';

        self::recursiveCopy($webDir.'/uploads.dist', $webDir.'/uploads');

        copy($webDir.'/app.php.dist',         $webDir.'/app.php');
        copy($webDir.'/etu_dev.php.dist',     $webDir.'/etu_dev.php');

        echo "Done\n";
    }

    public static function changePermissions($rootDir)
    {
        $appDir = $rootDir.'/app';
        $webDir = $rootDir.'/web';

        // Cache
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($appDir.'/cache'));

        foreach ($iterator as $item) {
            chmod($item, 0777);
        }

        // Logs
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($appDir.'/logs'));

        foreach ($iterator as $item) {
            chmod($item, 0777);
        }

        // Uploads
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($webDir.'/uploads'));

        foreach ($iterator as $item) {
            chmod($item, 0777);
        }

        echo "Done\n";
    }

    public static function createDatabase()
    {
        passthru('php app/console doctrine:schema:update --force --env=dev');
    }

    public static function insertBasicData()
    {
        echo "Inserting badges ...\n";
        passthru('php app/console etu:badges:import --env=dev');

        echo "Inserting cities ...\n";
        passthru('php app/console etu:cities:import --env=dev');
    }

    public static function createFisrtUser($config)
    {
        echo "Creating first user ...\n";
        passthru(sprintf(
            'php app/console etu:users:create --env=dev --login="%s" --firstName="%s" '.
            '--lastName="%s" --password="%s" --email="%s"',
            $config['first_user']['login'],
            $config['first_user']['firstName'],
            $config['first_user']['lastName'],
            $config['first_user']['password'],
            $config['first_user']['email']
        ));
    }

    public static function installBower($rootDir)
    {
        chdir($rootDir);
        passthru('bower install --force');
        chdir($rootDir);
    }

    public static function installAssets()
    {
        passthru('php app/console assets:install web --env=dev');
        passthru('php app/console assetic:dump --env=dev');
    }

    private static function recursiveCopy($source, $destination)
    {
        if (is_dir($source)) {
            @mkdir($destination);
            $directory = dir($source);

            while (($readdirectory = $directory->read()) !== false) {
                if ($readdirectory == '.' || $readdirectory == '..') {
                    continue;
                }

                $pathDir = $source.'/'.$readdirectory;

                if (is_dir($pathDir)) {
                    self::recursiveCopy($pathDir, $destination.'/'.$readdirectory);
                    continue;
                }

                copy($pathDir, $destination.'/'.$readdirectory);
            }

            $directory->close();
        } else {
            copy($source, $destination);
        }
    }

    private static function prompt()
    {
        return trim(fgets(fopen('php://stdin', 'r')));
    }
}
