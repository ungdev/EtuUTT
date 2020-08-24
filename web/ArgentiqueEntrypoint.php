<?php

require_once '../app/autoload.php';
use Etu\Module\ArgentiqueBundle\Glide\ImageBuilder;
use Firebase\JWT\JWT;

/**
 * This file is the entrypoint for argentique images
 * For more information see readme.md in argentique bundle.
 */

// Config
$regex = '/^\/argentique\/photo\/([^\?]+)\??.*$/';
$jwtAlgo = 'HS256';
$jwtKey = $_ENV['ETUUTT_ARGENTIQUE_JWT'];

// Check config
if (empty($jwtKey)) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'La configuration du serveur n\'est pas complète. Veuillez ajouter une valeur pour `argentique_jwt_key` dans la configuration.';
    exit;
}

// Check if URI is in view_argentique routes by matching regex
$match = preg_match($regex, $_SERVER['REQUEST_URI'], $matches);
$path = urldecode($matches[1] ?? '');
if (!$match || preg_match('/(^|[\/])\.\.($|[\/])/', $path)) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Le serveur est probablement mal configuré. ArgentiqueEntrypoint.php ne doit pas être utilisé pour les URI ne correspondant pas à des images d\'argentique.';
    exit;
}

// Check if user is authorized by cookie
$authorized = false;
try {
    $authorized = JWT::decode($_COOKIE['external_jwt'] ?? '', $jwtKey, [$jwtAlgo])->ROLE_ARGENTIQUE_READ;
} catch (\Exception $e) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Vous n\'avez pas le droit de visualiser cette image. Veuillez vous connecter à EtuUTT.';
    exit;
}
if (!$authorized) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Vous n\'avez pas le droit de visualiser cette image. Veuillez vous connecter à EtuUTT.';
    exit;
}

// Build image
ImageBuilder::createImageResponse($path, $_GET['mode'] ?? '')->send();
