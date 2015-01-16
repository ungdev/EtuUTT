<?php

/**
 * Use Glide (image manipulation library) to serve protected images
 * Each image can be customized using GET parameters
 * Bypass Symfony for faster loading
 */



require __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use League\Glide\Factories\Server;
use League\Glide\Factories\HttpSignature;
use League\Glide\Exceptions\InvalidSignatureException;
use League\Glide\Exceptions\ImageNotFoundException;



/*
 * Configuration
 */
$parameters = \Symfony\Component\Yaml\Yaml::parse(__DIR__ . '/../../app/config/parameters.yml')['parameters'];

$config = [
    'secret' => $parameters['secret'],
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


// HTTP signature
try {
    HttpSignature::create($config['secret'])->validateRequest($request);
} catch (InvalidSignatureException $e) {
    $response = new Response($e->getMessage(), 403);
    $response->send();
    exit;
}



/*
 * Render image
 */

$glide = Server::create([
    'source' => $config['source'],
    'cache' => __DIR__ . '/cache',
]);

try {
    $glide->outputImage($request);
} catch (ImageNotFoundException $e) {
    $response = new Response($e->getMessage(), 404);
    $response->send();
    exit;
}
