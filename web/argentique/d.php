<?php

/**
 * Use Imagine to create a thumbnail of a directory using the first images inside
 * Bypass Symfony for faster loading
 */



require __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Imagine\Gd\Imagine;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;


/*
 * Configuration
 */
$parameters = \Symfony\Component\Yaml\Yaml::parse(__DIR__ . '/../../app/config/parameters.yml')['parameters'];

$config = [
    'source' => __DIR__ . '/../../src/Etu/Module/ArgentiqueBundle/Resources/photos',
    'cache' =>  __DIR__ . '/cache',
];



/*
 * PDO
 */
$pdo = new \PDO(
    sprintf('mysql:host=%s;dbname=%s', $parameters['database_host'], $parameters['database_name']),
    $parameters['database_user'],
    $parameters['database_password']
);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);




/*
 * Request validation
 */
$_SERVER['REQUEST_URI'] = urldecode($_SERVER['REQUEST_URI']);
$request = Request::createFromGlobals();

// User authenticated?
$userCookie = $request->cookies->get(md5('etuutt-session-cookie-name'));

if (! $userCookie) {
    $response = new Response('Anonymous access not authorized', 403);
    $response->send();
    exit;
}

$query = $pdo->prepare('SELECT * FROM etu_users_sessions WHERE token = ?');
$query->execute([ $userCookie ]);

$session = $query->fetch(\PDO::FETCH_ASSOC);

if (! $session || \DateTime::createFromFormat('Y-m-d H:i:s', $session['expireAt']) <= new \DateTime()) {
    $response = new Response('Session expired', 403);
    $response->send();
    exit;
}

// DIrectory exists?
if (! file_exists($config['source'] . $request->getPathInfo())) {
    $response = new Response('Not found', 404);
    $response->send();
    exit;
}



/*
 * Image render
 */
$imagine = new Imagine();

$cacheFile = __DIR__ . '/cache/' . md5($request->getPathInfo()) . '.png';

if (file_exists($cacheFile)) {
    $imagine->open($cacheFile)->show('png');
    exit;
}

/** @var \SplFileInfo[]|\RecursiveIteratorIterator $iterator */
$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($config['source'] . $request->getPathInfo()));

$photo = false;

foreach ($iterator as $file) {
    if ($file->getExtension() == 'jpg' || $file->getExtension() == 'jpeg') {
        $size = getimagesize($file->getPathname());

        // Landscape images only
        if ($size[0] > $size[1]) {
            $photo = $file->getPathname();
            break;
        }
    }
}

$image = $imagine->open(__DIR__ . '/../src/img/dirmask.png');

if ($photo) {
    $photo = $imagine->open($photo)->thumbnail(new Box(300, 200), ImageInterface::THUMBNAIL_OUTBOUND);
    $image->paste($photo, new Point(36, 48));
}

$image = $image->thumbnail(new Box(132, 88), ImageInterface::THUMBNAIL_OUTBOUND);

if ($photo) {
    $image->save($cacheFile);
}

$image->show('png');
