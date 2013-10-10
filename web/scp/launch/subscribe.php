<?php

include 'config.php';

if ($now >= $launch) {
	echo json_encode(array('status' => 'error', 'content' => 'Le site est déjà sorti !'));
	exit;
}

if (! $acceptTesters) {
	echo json_encode(array('status' => 'error', 'content' => 'Nous n\'acceptons plus de bêta-testeurs'));
	exit;
}

function get_ip_address() {
	$serverKeys = array(
		'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED', 'REMOTE_ADDR'
	);

	foreach ($serverKeys as $key) {
		if (array_key_exists($key, $_SERVER) === true) {
			foreach (explode(',', $_SERVER[$key]) as $ip) {
				if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
					return $ip;
				}
			}
		}
	}

	return $_SERVER['REMOTE_ADDR'];
}

if (! isset($_GET['email'])) {
	echo json_encode(array('status' => 'error', 'content' => 'L\'adresse email est requise'));
	exit;
}

$email = $_GET['email'];
$ip = get_ip_address();

if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
	echo json_encode(array('status' => 'error', 'content' => 'Cette adresse email n\'est pas valide'));
	exit;
}

if (! file_exists('etuutt/registry.json')) {
	file_put_contents('etuutt/registry.json', json_encode(array()));
}

if (! file_exists('etuutt/spam.json')) {
	file_put_contents('etuutt/spam.json', json_encode(array()));
}

$registry = json_decode(file_get_contents('etuutt/registry.json'), true);
$spam = json_decode(file_get_contents('etuutt/spam.json'), true);

if (isset($registry[$email])) {
	echo json_encode(array('status' => 'error', 'content' => 'Cette adresse email est déjà enregistrée'));
	exit;
}

if (isset($spam[$ip]) && $spam[$ip] + 15 > time()) {
	echo json_encode(array(
		'status' => 'error',
		'content' => 'Vous ne pouvez enregistrer qu\'une adresse toutes les 15 secondes par IP'
	));
	exit;
}

$registry[$email] = $ip;
$spam[$ip] = time();

file_put_contents('etuutt/registry.json', json_encode($registry));
file_put_contents('etuutt/spam.json', json_encode($spam));

echo json_encode(array('status' => 'success', 'content' => 'Vous êtes désormais inscrit au bêta-testing'));
