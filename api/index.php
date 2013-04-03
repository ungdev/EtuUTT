<?php


/********************************
 * Database
 ********************************/
$pdo = new PDO('mysql:host=localhost;dbname=etuutt_api', 'root', '');

const TABLE_NAME = 'vue_edt_etu';



/********************************
 * Application
 ********************************/

require __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();


// ***************************************************

$app->get('/', function() use ($app, $pdo) {

	$list = $pdo->query('
		SELECT weekname, id, seance_edt_id_liee, etu_id, nom_prenom, branche, semaine, uv, type, jour, debut, fin
		FROM '.TABLE_NAME
	);

	return json_encode($list->fetchAll());
});

// ***************************************************

$app->get('/student/{etuId}', function($etuId) use ($app, $pdo) {

	$list = $pdo->prepare('
		SELECT weekname, id, seance_edt_id_liee, etu_id, nom_prenom, branche, semaine, uv, type, jour, debut, fin
		FROM '.TABLE_NAME.'
		WHERE etu_id = ?'
	);

	$list->execute(array($etuId));

	return json_encode($list->fetchAll());
});

// ***************************************************

$app->get('/name/{name}', function($name) use ($app, $pdo) {

	$list = $pdo->prepare('
		SELECT weekname, id, seance_edt_id_liee, etu_id, nom_prenom, branche, semaine, uv, type, jour, debut, fin
		FROM '.TABLE_NAME.'
		WHERE nom_prenom LIKE ?'
	);

	$list->execute(array('%'.$name.'%'));

	return json_encode($list->fetchAll());
});

// ***************************************************

$app->get('/uv/{uv}', function($uv) use ($app, $pdo) {

	$list = $pdo->prepare('
		SELECT weekname, id, seance_edt_id_liee, etu_id, nom_prenom, branche, semaine, uv, type, jour, debut, fin
		FROM '.TABLE_NAME.'
		WHERE uv = ?'
	);

	$list->execute(array($uv));

	return json_encode($list->fetchAll());
});

// ***************************************************

$app->get('/day/{jour}', function($jour) use ($app, $pdo) {

	$list = $pdo->prepare('
		SELECT weekname, id, seance_edt_id_liee, etu_id, nom_prenom, branche, semaine, uv, type, jour, debut, fin
		FROM '.TABLE_NAME.'
		WHERE jour = ?
	');

	$list->execute(array($jour));

	return json_encode($list->fetchAll());
});

// ***************************************************

$app->get('/day/{jour}/{startHour}/{startMinute}', function($jour, $startHour, $startMinute) use ($app, $pdo) {

	$list = $pdo->prepare('
		SELECT weekname, id, seance_edt_id_liee, etu_id, nom_prenom, branche, semaine, uv, type, jour, debut, fin
		FROM '.TABLE_NAME.'
		WHERE jour = ? AND debut = ?
	');

	$list->execute(array($jour, $startHour.':'.$startMinute));

	return json_encode($list->fetchAll());
});

// ***************************************************

$app->get('/day/{jour}/{startHour}/{startMinute}/{endHour}/{endMinute}',
	function($jour, $startHour, $startMinute, $endHour, $endMinute) use ($app, $pdo) {

	$list = $pdo->prepare('
		SELECT weekname, id, seance_edt_id_liee, etu_id, nom_prenom, branche, semaine, uv, type, jour, debut, fin
		FROM '.TABLE_NAME.'
		WHERE jour = ? AND debut = ? AND fin = ?
	');

	$list->execute(array($jour, $startHour.':'.$startMinute, $endHour.':'.$endMinute));

	return json_encode($list->fetchAll());
});

$app->run();